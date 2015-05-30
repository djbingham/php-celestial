<?php

namespace Sloth\Test\Unit\Database;

use Sloth;

class UnitTest extends Sloth\Test\UnitTest
{
	private $mockBuilder;

    public function mockBuilder()
	{
		if (is_null($this->mockBuilder)) {
			$this->mockBuilder = new MockBuilder();
		}
		return $this->mockBuilder;
	}
}
