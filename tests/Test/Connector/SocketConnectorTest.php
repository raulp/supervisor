<?php

namespace Indigo\Supervisor\Test\Connector;

use Indigo\Supervisor\Connector\SocketConnector;

abstract class SocketConnectorTest extends ConnectorTest
{
    public function testMethodCreateSocket()
    {
        $method = new \ReflectionMethod(get_class($this->connector), 'createSocket');
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @depends testMethodCreateSocket
     * @expectedException RuntimeException
     */
    public function testFaultCreateSocket(\ReflectionMethod $method)
    {
        $method->invoke($this->connector, 'fake');
    }

    /**
     * @depends testMethodCreateSocket
     */
    public function testCreateSocket(\ReflectionMethod $method)
    {
        $resource = $method->invoke($this->connector, 'google.hu', 80);

        $this->assertTrue(is_resource($resource));
    }

    /**
     * @depends testMethodCreateSocket
     */
    public function testCreatePersistentSocket(\ReflectionMethod $method)
    {
        $resource = $method->invoke($this->connector, 'google.hu', 80, null, true);

        $this->assertTrue(is_resource($resource));
    }

    public function testPersistent()
    {
        $this->assertTrue(is_bool($this->connector->isPersistent()));
    }

    public function testTimeout()
    {
        $timeout = $this->connector->setTimeout(null);

        if ($this->connector->isConnected()) {
            $this->assertTrue($timeout);
        } else {
            $this->assertFalse($timeout);
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTimeoutFailure()
    {
        $this->connector->setTimeout('null');
    }

    public function testResource()
    {
        if ($this->connector->isConnected()) {
            $this->assertTrue(is_resource($resource = $this->connector->getResource()));

            $this->assertInstanceOf(
                'Indigo\\Supervisor\\Connector\\SocketConnector',
                $this->connector->setResource($resource)
            );
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testResourceFailure()
    {
        $this->connector->setResource(null);
    }

    public function testPrepareRequest()
    {
        $method = new \ReflectionMethod(get_class($this->connector), 'prepareRequest');
        $method->setAccessible(true);

        $this->assertInstanceOf(
            'Buzz\\Message\\RequestInterface',
            $method->invoke($this->connector, 'namespace', 'method', array())
        );
    }

    public function testPrepareClient()
    {
        $method = new \ReflectionMethod(get_class($this->connector), 'prepareClient');
        $method->setAccessible(true);

        $this->assertInstanceOf(
            'Buzz\\Client\\ClientInterface',
            $method->invoke($this->connector)
        );
    }

    public function testClose()
    {
        $connector = clone $this->connector;
        $connector->close();

        $this->assertFalse($connector->isConnected());

        $timeout = $this->connector->setTimeout(null);
        $this->assertFalse($timeout);
    }

    public function testDestruct()
    {
        $connector = clone $this->connector;
        unset($connector);
    }

    public function tearDown()
    {
        unset($this->connector);
    }
}
