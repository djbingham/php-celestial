<?php
namespace Sloth\Module\Data\TableQuery\Test\Mock;

use PhpMySql\Face\QueryInterface;

class Connection extends \PhpMySql\Connection\MySqli
{
	/**
	 * @var array
	 */
	protected $expectedQuerySequence = array();

	/**
	 * @var array
	 */
	protected $mockGetDataSequence = array();

	/**
	 * @var mixed Data to return on next call to getData
	 */
	protected $nextData;

	/**
	 * @var array
	 */
	protected $mockLastInsertIdSequence = array();

	/**
	 * @var integer Value to return on next call to getLastInsertId
	 */
	protected $lastInsertId;

	/**
	 * @param QueryInterface $query
	 * @return $this
	 * @throws \Exception if query doesn't match next expected query
	 */
	public function executeQuery(QueryInterface $query)
	{
		$this->assertEqualsNextExpectedQuery((string) $query);
		array_shift($this->expectedQuerySequence);

		// If next response data is an exception, throw it
		if (count($this->mockGetDataSequence) > 0) {
			$this->lastInsertId = array_shift($this->mockLastInsertIdSequence);
			$this->nextData = array_shift($this->mockGetDataSequence);
			if ($this->nextData instanceof \Exception) {
				throw $this->nextData;
			}
		} else {
			unset($this->nextData);
		}

		return $this;
	}

	public function getLastResultData()
	{
		$response = $this->nextData;
		if ($response instanceof \Exception) {
			throw $response;
		}
		return $response;
	}

	public function getLastInsertId()
	{
		return $this->lastInsertId;
	}

	public function connect($host = null, $user = null, $password = null, $database = null, $port = null, $socket = null)
	{
		return true;
	}

	public function autocommit($mode)
	{
		return true;
	}

	public function begin($flags = null, $name = null)
	{
		$this->assertNextExpectedResponseEquals(true);
		array_shift($this->expectedQuerySequence);
		array_shift($this->mockGetDataSequence);
		return true;
	}

	public function commit($flags = null, $name = null)
	{
		$this->assertNextExpectedResponseEquals(true);
		array_shift($this->expectedQuerySequence);
		array_shift($this->mockGetDataSequence);
		return true;
	}

	public function rollback($flags = null, $name = null)
	{
		$this->assertNextExpectedResponseEquals(true);
		array_shift($this->expectedQuerySequence);
		array_shift($this->mockGetDataSequence);
		return true;
	}

	/**
	 * @throws \Exception if expecting queries to be run
	 */
	public function assertNotExpectingQueries()
	{
		if (!empty($this->expectedQuerySequence)) {
			throw new \Exception('Expected queries not run: ' . print_r($this->expectedQuerySequence, true));
		}
	}

	/**
	 * @param array $queryStrings
	 * @return $this
	 */
	public function expectQuerySequence(array $queryStrings)
	{
		$this->expectedQuerySequence = $queryStrings;
		return $this;
	}

	/**
	 * @param string $queryString
	 * @return $this
	 */
	public function expectQuery($queryString)
	{
		$this->expectedQuerySequence = array($queryString);
		return $this;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function pushQueryResponse($data)
	{
		$this->mockGetDataSequence[] = $data;
		return $this;
	}

	/**
	 * @param integer $id
	 * @return $this
	 */
	public function pushInsertId($id)
	{
		$this->mockLastInsertIdSequence[] = $id;
		return $this;
	}

	protected function assertEqualsNextExpectedQuery($actual)
	{
		$nextQuery = null;
		if (count($this->expectedQuerySequence) > 0) {
			$nextQuery = $this->expectedQuerySequence[0];
		}

		// Check that the query matches the next expected query string
		if ($nextQuery !== $actual) {
			$message = sprintf(
				"Failed asserting that query equals next expected query.\r\nActual: %s.\r\nExpected: %s",
				$actual,
				(string) $nextQuery
			);
			throw new \Exception($message);
		}
		return true;
	}

	protected function assertNextExpectedResponseEquals($expected)
	{
		$nextResponse = '';
		if (count($this->mockGetDataSequence) > 0) {
			$nextResponse = $this->mockGetDataSequence[0];
		}

		if ($expected instanceof \Exception && $nextResponse instanceof \Exception) {
			return true;
		}

		if ($expected === $nextResponse) {
			return true;
		}
		$message = "Expected and actual next response do not match. Expected: %s\nActual: %s";
		throw new \Exception(sprintf($message, print_r($expected, true), print_r($nextResponse, true)));
	}

	public function escapeString($string)
	{
		return $string;
	}
}
