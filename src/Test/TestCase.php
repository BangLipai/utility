<?php

namespace BangLipai\Utility\Test;

use BangLipai\Utility\ServiceProvider;
use BangLipai\Utility\Test\Trait\LazilyRefreshDatabase;
use BangLipai\Utility\Test\Trait\LogRequest;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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
