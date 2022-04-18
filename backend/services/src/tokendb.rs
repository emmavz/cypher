use crate::error::{TokenStateError, TokenStateResult};
use futures::executor::block_on;
use gluesql::prelude::{parse, plan, translate, PayloadVariable, Value};
use gluesql::{
	core::ast::Statement,
	prelude::{Glue, Payload},
	sled_storage::{sled::IVec, SledStorage},
};
use std::collections::HashMap;
use std::path::{Path, PathBuf};

const BEGIN_TRANSACTION_EXPR: &str = "BEGIN;";
const COMMIT_TRANSACTION_EXPR: &str = "COMMIT;";
const ROLLBACK_TRANSACTION_EXPR: &str = "ROLLBACK;";

pub type SledGlue = Glue<IVec, SledStorage>;

pub struct TokenDb {
	pub tsid: Tsid,
	pub db_map: HashMap<TokenId, SledGlue>,
	pub parent_dir: String,
}

impl TokenDb {
	pub fn new(parent_dir: String) -> Self {
		TokenDb {
			parent_dir,
			tsid: Tsid::genesis(),
			db_map: HashMap::new(),
		}
	}

	fn token_db_path(&self, token_id: TokenId) -> PathBuf {
		Path::new(self.parent_dir.as_str()).join(format!("{}", token_id))
	}

	pub fn init_glue(&mut self, token_id: TokenId) -> TokenStateResult<()> {
		info!("init_glue");
		if let Some(_) = self.db_map.get_mut(&token_id) {
			info!("token_id {} exists in glue db_map", token_id);
			return Err(TokenStateError::Other(format!(
				"Can not init twice for {}",
				token_id
			)));
		} else {
			info!("now insert token_id {} into glue map", token_id);
			self.db_map.insert(
				token_id,
				SledGlue::new(SledStorage::new(
					self.token_db_path(token_id)
						.to_str()
						.ok_or(TokenStateError::Other(format!(
							"token {} glue sql path invalid",
							token_id
						)))?,
				)?),
			);
			Ok(())
		}
	}

	pub fn begin_transaction(&mut self, token_id: TokenId) -> TokenStateResult<Vec<u8>> {
		let payload = self
			.db_map
			.get_mut(&token_id)
			.ok_or(TokenStateError::DbNotFound(token_id))?
			.execute(BEGIN_TRANSACTION_EXPR)?;
		info!("begin transaction result: {:?}", payload);
		Ok(bincode::serialize(&payload)?)
	}

	pub fn check_transaction_context(&self, buf: &[u8]) -> TokenStateResult<()> {
		// todo check real context instead of serialize buffer later
		let start_buf = bincode::serialize(&Payload::StartTransaction)?;
		if !start_buf.eq(buf) {
			return Err(TokenStateError::InvalidTransactionContext);
		}
		Ok(())
	}

	pub fn commit_transaction(&mut self, token_id: TokenId) -> TokenStateResult<()> {
		let payload = self
			.db_map
			.get_mut(&token_id)
			.ok_or(TokenStateError::DbNotFound(token_id))?
			.execute(COMMIT_TRANSACTION_EXPR)?;
		info!("commit transaction result: {:?}", payload);
		Ok(())
	}

	pub fn rollback_transaction(&mut self, token_id: TokenId) -> TokenStateResult<()> {
		let payload = self
			.db_map
			.get_mut(&token_id)
			.ok_or(TokenStateError::DbNotFound(token_id))?
			.execute(ROLLBACK_TRANSACTION_EXPR)?;
		info!("rollback transaction result: {:?}", payload);
		Ok(())
	}

	pub fn exec_cmd(
		&mut self,
		token_id: TokenId,
		sql: &str,
		tsid: Tsid,
	) -> TokenStateResult<Vec<u8>> {
		if self.tsid > tsid {
			error!(
				"self.tsid >= tsid. SQL updated: tsid:{:?}, sql:{}",
				&tsid, sql
			);
			return Err(TokenStateError::Other(format!("sql {} is outdated", sql)));
		}

		let glue = self
			.db_map
			.get_mut(&token_id)
			.ok_or(TokenStateError::DbNotFound(token_id))?;

		let mut payloads = Vec::new();
		let statements = parse(sql)?;
		for s in statements.iter() {
			let mut statement = translate(s)?;
			statement = block_on(plan(glue.storage.as_ref().unwrap(), statement))?;
			let payload = glue.execute_stmt(statement)?;
			payloads.push(payload);
		}

		self.tsid = tsid;
		Ok(bincode::serialize(&payloads)?)
	}

	pub fn exec_query(&mut self, token_id: TokenId, sql: &str) -> TokenStateResult<Vec<u8>> {
		info!("enter exec_query, {}, sql: {}", token_id, sql);
		let glue = self
			.db_map
			.get_mut(&token_id)
			.ok_or(TokenStateError::DbNotFound(token_id))?;

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

	pub fn dump_gluedb_data(
		&mut self,
		token_id: TokenId,
		max_rows: Option<u64>,
	) -> TokenStateResult<HashMap<String, serde_json::Value>> {
		let mut glue = self
			.db_map
			.get_mut(&token_id)
			.ok_or(TokenStateError::DbNotFound(token_id))?;

		let payload = glue.execute("SHOW TABLES;")?;
		match payload {
			Payload::ShowVariable(PayloadVariable::Tables(tables)) => {
				let mut table_values = HashMap::new();
				for table in tables.iter() {
					table_values.insert(
						table.clone(),
						dump_single_table(table, max_rows.as_ref(), &mut glue)?,
					);
				}
				Ok(table_values)
			}
			_ => Err(TokenStateError::Other(format!("failed to tables variable"))),
		}
	}

	pub fn save(&self) -> TokenStateResult<Vec<u8>> {
		// let mut kvv: Vec<(TokenId, SledStorage)> = Vec::new();
		// for (token_id, mem_glue) in self.db_map.iter() {
		// 	kvv.push((*token_id, mem_glue.storage.clone().unwrap()));
		// }
		// let buf = bincode::serialize(&TokenDbPersist {
		// 	kvv,
		// 	tsid: self.tsid.clone(),
		// })?;
		// Ok(buf)
		Ok(vec![])
	}

	pub fn load(&mut self, _buf: &Vec<u8>) -> TokenStateResult<()> {
		// let persist: TokenDbPersist = bincode::deserialize(buf)?;
		// self.db_map = persist
		// 	.kvv
		// 	.iter()
		// 	.map(|(token_id, mem_storage)| (*token_id, SledGlue::new(mem_storage.clone())))
		// 	.collect();
		// self.tsid = persist.tsid;
		Ok(())
	}
}

// #[derive(Serialize, Deserialize)]
// struct TokenDbPersist {
// 	pub kvv: Vec<(TokenId, MemoryStorage)>,
// 	pub tsid: Tsid,
// }

fn dump_single_table(
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
	use crate::tokendb::dump_single_table;
	use crate::TokenDb;
	use interface::{Followup, Ts, Tsid};
	use serde_json::json;

	#[test]
	fn transaction_works() -> Result<(), Box<dyn std::error::Error>> {
		let temp = tempfile::tempdir().unwrap();
		let token_id = 1;
		let mut db = TokenDb::new(temp.path().to_str().unwrap().to_string());
		db.init_glue(token_id)?;

		db.exec_cmd(
			token_id,
			r#"
				CREATE TABLE TxTest (
            id INTEGER,
            name TEXT
        );
        INSERT INTO TxTest VALUES
            (1, "Friday"),
            (2, "Phone");
		"#,
			new_tsid(1),
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
			dump_single_table("TxTest", None, db.db_map.get_mut(&token_id).unwrap())?
		);

		db.begin_transaction(token_id)?;
		db.exec_cmd(
			token_id,
			r#"INSERT INTO TxTest VALUES (3, "Vienna");"#,
			new_tsid(2),
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
			dump_single_table("TxTest", None, db.db_map.get_mut(&token_id).unwrap())?
		);
		db.rollback_transaction(token_id)?;
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
			dump_single_table("TxTest", None, db.db_map.get_mut(&token_id).unwrap())?
		);

		db.begin_transaction(token_id)?;
		db.exec_cmd(
			token_id,
			r#"INSERT INTO TxTest VALUES (3, "Vienna");"#,
			new_tsid(3),
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
			dump_single_table("TxTest", None, db.db_map.get_mut(&token_id).unwrap())?
		);
		db.commit_transaction(token_id)?;
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
			dump_single_table("TxTest", None, db.db_map.get_mut(&token_id).unwrap())?
		);

		temp.close()?;
		Ok(())
	}

	fn new_tsid(ts: Ts) -> Tsid {
		Tsid::from_followup(
			Default::default(),
			&Followup {
				ts,
				..Default::default()
			},
		)
	}
}
