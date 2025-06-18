<?php

declare(strict_types=1);

namespace EventDispatcher\Tests\Unit\Dispatcher;

use PHPUnit\Framework\TestCase;
use Zepzeper\Torol\Dispatcher\EventDispatcher;
use Zepzeper\Torol\Event\AbstractEvent as ZepzeperAbstractEvent;

// Define a simple test event class
class TestEvent extends ZepzeperAbstractEvent
{
	public string $message;

	public function __construct(string $message)
	{
		$this->message = $message;
	}
}

// Define a simple test event listener class
class TestListener
{
	public string $receivedMessage = '';
	public bool $wasCalled = false;

	public function onTestEvent(TestEvent $event): void
	{
		$this->receivedMessage = $event->message;
		$this->wasCalled = true;
	}
}

class EventDispatcherTest extends TestCase
{
	public function testAddListenerAndDispatcherBasicEvent(): void
	{
		$dispatcher = new EventDispatcher();
		$listener = new TestListener();
		$event = new TestEvent("Hallo!");

		$dispatcher->addListener(TestEvent::class, [$listener, 'onTestEvent']);

		$dispatchedEvent = $dispatcher->dispatch($event);

		$this->assertTrue($listener->wasCalled, 'Listener should have been called.');
		$this->assertEquals('Hallo!', $listener->receivedMessage, 'Listener should receive the correct message.');
		$this->assertSame($event, $dispatchedEvent, 'The dispatched event object should be the same as the original.');
		$this->assertFalse($dispatchedEvent->isPropagationStopped(), 'Event propagation should not be stopped initially.');
	}

	public function testPropagationCanBeStopped(): void
	{
		$dispatcher = new EventDispatcher();
		$listener1 = $this->createMock(TestListener::class);
		$listener2 = $this->createMock(TestListener::class);
		$event = new TestEvent("Stoppable Hallo!");

		$listener1->expects($this->once())
			->method('onTestEvent')
			->willReturnCallback(fn (TestEvent $e) => $e->stopPropagation());

		$listener2->expects($this->never())
			->method('onTestEvent');

		$dispatcher->addListener(TestEvent::class, [$listener1, 'onTestEvent']);
		$dispatcher->addListener(TestEvent::class, [$listener2, 'onTestEvent']);

		$dispatchedEvent = $dispatcher->dispatch($event);

		$this->assertTrue($dispatchedEvent->isPropagationStopped(), 'Event propagation should be stopped.');
	}
}
