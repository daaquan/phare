<?php

namespace Feature;

use Tests\TestCase;

class IndexTest extends TestCase
{
    public function test_index_response_and_status_code()
    {
        $response = $this->get('/version');

        $this->assertEquals(env('APP_VER'), $response->getContent());
    }
}