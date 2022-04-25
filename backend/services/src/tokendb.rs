use crate::error::{TokenStateError, TokenStateResult};
use futures::executor::block_on;
use gluesql::prelude::{parse, plan, translate, Value};
use gluesql::{
	core::ast::Statement,
	prelude::{Glue, Payload},
	sled_storage::{sled::IVec, SledStorage},
};
use std::path::Path;

const BEGIN_TRANSACTION_EXPR: &str = "BEGIN;";
const COMMIT_TRANSACTION_EXPR: &str = "COMMIT;";
const ROLLBACK_TRANSACTION_EXPR: &str = "ROLLBACK;";
pub const ROW_KEY_OF_EMPTY_TABLE: &str = "<headers>";
pub type SledGlue = Glue<IVec, SledStorage>;


	// fn token_db_path(&self, token_id: TokenId) -> PathBuf {
	// 	Path::new(self.parent_dir.as_str()).join(format!("{}", token_id))
	// }

pub fn init_glue(parent_dir: &str) -> TokenStateResult<SledGlue> {
	println!("init_glue to dir {}", parent_dir);
	Ok(SledGlue::new(SledStorage::new(
		Path::new(parent_dir).join("cypher_db")
		.to_str()
		.ok_or(TokenStateError::Other(format!(
			"glue sql path invalid"
		)))?,
	)?))
}

pub fn begin_transaction(glue: &mut SledGlue) -> TokenStateResult<Vec<u8>> {
	let payload = glue.execute(BEGIN_TRANSACTION_EXPR)?;
	println!("begin transaction result: {:?}", payload);
	Ok(bincode::serialize(&payload)?)
}

pub fn check_transaction_context(buf: &[u8]) -> TokenStateResult<()> {
	// todo check real context instead of serialize buffer later
	let start_buf = bincode::serialize(&Payload::StartTransaction)?;
	if !start_buf.eq(buf) {
		return Err(TokenStateError::InvalidTransactionContext);
	}
	Ok(())
}

pub fn commit_transaction(glue: &mut SledGlue) -> TokenStateResult<()> {
	let payload = glue.execute(COMMIT_TRANSACTION_EXPR)?;
	println!("commit transaction result: {:?}", payload);
	Ok(())
}

pub fn rollback_transaction(glue: &mut SledGlue) -> TokenStateResult<()> {
	let payload = glue.execute(ROLLBACK_TRANSACTION_EXPR)?;
	println!("rollback transaction result: {:?}", payload);
	Ok(())
}

pub fn exec_cmd(
	glue: &mut SledGlue,
	sql: &str,
) -> TokenStateResult<Vec<u8>> {
	let mut payloads = Vec::new();
	let statements = parse(sql)?;
	for s in statements.iter() {
		let mut statement = translate(s)?;
		statement = block_on(plan(glue.storage.as_ref().unwrap(), statement))?;
		let payload = glue.execute_stmt(statement)?;
		payloads.push(payload);
	}

	Ok(bincode::serialize(&payloads)?)
}

pub fn exec_query(glue: &mut SledGlue, sql: &str) -> TokenStateResult<Vec<u8>> {
	println!("enter exec_query, sql: {}", sql);
	let statement: Statement = block_on(glue.plan(sql))?;
	match statement {
		Statement::Query(_) => (),
		_ => {
			return Err(TokenStateError::Other(format!(
				"Only query statement is allowed in query."
			)))
		}
	}

	let result: Payload = glue.execute_stmt(statement)?;
	Ok(bincode::serialize(&result)?)
}
pub fn dump_single_table(
	table: &str,
	max_rows: Option<&u64>,
	glue: &mut SledGlue,
) -> TokenStateResult<serde_json::Value> {
	let mut values = Vec::new();

	let payload = glue.execute(&format!("SELECT * FROM {};", table))?;
	match payload {
		Payload::Select { labels, rows } => {
			if rows.is_empty() {
				// if there is no rows just print headers
				values.push(serde_json::json!({
					ROW_KEY_OF_EMPTY_TABLE: labels,
				}));
			} else {
				for row in rows.iter().take(*max_rows.unwrap_or(&u64::MAX) as usize) {
					let mut record = serde_json::Map::new();

					for (i, field) in row.iter().enumerate() {
						record.insert(
							labels
								.get(i)
								.ok_or(TokenStateError::Other(format!(
									"failed to get label at index {}",
									i
								)))?
								.clone(),
							sql_to_json_value(field),
						);
					}
					values.push(serde_json::Value::Object(record));
				}
			}
		}
		_ => {
			return Err(TokenStateError::Other(format!(
				"Get not select result: {:?}",
				payload
			)))
		}
	}

	Ok(serde_json::json!(values))
}

// pub fn dump_gluedb_data(
// 	glue: &Glue,
// 	max_rows: Option<u64>,
// ) -> TokenStateResult<HashMap<String, serde_json::Value>> {
// 	let payload = glue.execute("SHOW TABLES;")?;
// 	match payload {
// 		Payload::ShowVariable(PayloadVariable::Tables(tables)) => {
// 			let mut table_values = HashMap::new();
// 			for table in tables.iter() {
// 				table_values.insert(
// 					table.clone(),
// 					dump_single_table(table, max_rows.as_ref(), &mut glue)?,
// 				);
// 			}
// 			Ok(table_values)
// 		}
// 		_ => Err(TokenStateError::Other(format!("failed to tables variable"))),
// 	}
// }

fn sql_to_json_value(v: &Value) -> serde_json::Value {
	match v {
		Value::Bool(b) => serde_json::Value::Bool(*b),
		Value::I8(i) => serde_json::Value::Number(serde_json::Number::from(*i)),
		Value::I64(i) => serde_json::Value::Number(serde_json::Number::from(*i)),
		Value::Str(s) => serde_json::Value::String(s.clone()),
		_ => serde_json::Value::String(format!("{:?}", v)),
	}
}

#[cfg(test)]
mod tests {
	use serde_json::json;
	use crate::tokendb::{init_glue, begin_transaction, rollback_transaction, 
		dump_single_table, exec_cmd, commit_transaction, 

	};
	#[test]
	fn transaction_works() -> Result<(), Box<dyn std::error::Error>> {
		let temp = tempfile::tempdir().unwrap();
		let mut db = init_glue(temp.path().to_str().unwrap())?;

		exec_cmd(
			&mut db,
			r#"
				CREATE TABLE TxTest (
            id INTEGER,
            name TEXT
        );
        INSERT INTO TxTest VALUES
            (1, "Friday"),
            (2, "Phone");
		"#,
		)?;
		assert_eq!(
			json!([
				{
					"id": 1,
					"name": "Friday"
				},
				{
					"id": 2,
					"name": "Phone"
				}
			]),
			dump_single_table("TxTest", None, &mut db)?
		);

		begin_transaction(&mut db)?;
		exec_cmd(
			&mut db,
			r#"INSERT INTO TxTest VALUES (3, "Vienna");"#,
		)?;
		assert_eq!(
			json!([
				{
					"id": 1,
					"name": "Friday"
				},
				{
					"id": 2,
					"name": "Phone"
				},
				{
					"id": 3,
					"name": "Vienna"
				}
			]),
			dump_single_table("TxTest", None, &mut db)?
		);
		rollback_transaction(&mut db)?;
		assert_eq!(
			json!([
				{
					"id": 1,
					"name": "Friday"
				},
				{
					"id": 2,
					"name": "Phone"
				}
			]),
			dump_single_table("TxTest", None, &mut db)?
		);

		begin_transaction(&mut db)?;
		exec_cmd(
			&mut db,
			r#"INSERT INTO TxTest VALUES (3, "Vienna");"#,
		)?;
		assert_eq!(
			json!([
				{
					"id": 1,
					"name": "Friday"
				},
				{
					"id": 2,
					"name": "Phone"
				},
				{
					"id": 3,
					"name": "Vienna"
				}
			]),
			dump_single_table("TxTest", None, &mut db)? 
		);
		commit_transaction(&mut db)?;
		assert_eq!(
			json!([
				{
					"id": 1,
					"name": "Friday"
				},
				{
					"id": 2,
					"name": "Phone"
				},
				{
					"id": 3,
					"name": "Vienna"
				}
			]),
			dump_single_table("TxTest", None, &mut db)?
		);

		temp.close()?;
		Ok(())
	}
}
