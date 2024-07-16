<?php

namespace BangLipai\Test;

use BangLipai\Test\Trait\LazilyRefreshDatabase;
use BangLipai\Test\Trait\LogRequest;
use BangLipai\Utility\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
   use LazilyRefreshDatabase;
   use LogRequest;

   public function setUp(): void
   {
      parent::setUp();

      // additional setup
   }

   protected function getPackageProviders($app): array
   {
      return [
         ServiceProvider::class,
      ];
   }

   protected function getEnvironmentSetUp($app)
   {
      // perform environment setup
   }
}
