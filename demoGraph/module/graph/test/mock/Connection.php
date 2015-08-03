<?php
namespace DemoGraph\Module\Graph\Test\Mock;

class Connection extends \SlothMySql\Connection\MySqli
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
	 * @param \SlothMySql\Abstractory\AQuery $query
	 * @return $this
	 * @throws \Exception if query doesn't match next expected query
	 */
	public function executeQuery(\SlothMySql\Abstractory\AQuery $query)
	{
		$this->assertEqualsNextExpectedQuery((string) $query);
		array_shift($this->expectedQuerySequence);

		// If next response data is an exception, throw it
		if (count($this->mockGetDataSequence) > 0) {
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

	public function connect()
	{
		return true;
	}

	public function autocommit()
	{
		return true;
	}

	public function begin()
	{
		$this->assertNextExpectedResponseEquals(true);
		array_shift($this->expectedQuerySequence);
		array_shift($this->mockGetDataSequence);
		return true;
	}

	public function commit()
	{
		$this->assertNextExpectedResponseEquals(true);
		array_shift($this->expectedQuerySequence);
		array_shift($this->mockGetDataSequence);
		return true;
	}

	public function rollback()
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
	 * @param mixed $data
	 * @return $this
	 */
	public function pushQueryResponse($data)
	{
		$this->mockGetDataSequence[] = $data;
		return $this;
	}

	protected function assertEqualsNextExpectedQuery($actual)
	{
		$nextQuery = '';
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
