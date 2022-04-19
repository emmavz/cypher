
use thiserror::Error;
#[derive(Error, Debug)]
pub enum TokenStateError {
	#[error("{0}")]
	Other(String),

	#[error("Glue db transaction is invalid")]
	InvalidTransactionContext,

	#[error(transparent)]
	BinCodeSerializeError(#[from] bincode::Error),

	#[error(transparent)]
	GlueSqlError(#[from] gluesql::core::result::Error),
}

pub type TokenStateResult<T> = Result<T, TokenStateError>;
