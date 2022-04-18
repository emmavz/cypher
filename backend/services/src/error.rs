
use thiserror::Error;
type TokenId = u116;
#[derive(Error, Debug)]
pub enum TokenStateError {
	#[error("{0}")]
	Other(String),

	#[error("failed to get sql db for tapp id: {0}")]
	DbNotFound(TokenId),

	#[error("Glue db transaction is invalid")]
	InvalidTransactionContext,

	#[error(transparent)]
	BinCodeSerializeError(#[from] bincode::Error),

	#[error(transparent)]
	GlueSqlError(#[from] gluesql::core::result::Error),
}

pub type TokenStateResult<T> = Result<T, TokenStateError>;
