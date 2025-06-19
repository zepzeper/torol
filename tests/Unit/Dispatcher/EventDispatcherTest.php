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

	public function __construct(string $message, array $data = [])
	{
		parent::__construct($data);
		$this->message = $message;
	}
}

// Define a simple test event listener class
class TestListener
{
	public string $receivedMessage = '';
	public bool $wasCalled = false;
	public array $executionOrder = [];

	public function onTestEvent(TestEvent $event): void
	{
		$this->wasCalled = true;
		$this->receivedMessage = $event->message;
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

	public function testEventSortingPriority(): void
	{
		$dispatcher = new EventDispatcher();
		$event = new TestEvent("Priority Test!");

		$callOrder = [];

		$listenerLowPriority = $this->createMock(TestListener::class);
		$listenerNormalPriority = $this->createMock(TestListener::class);
		$listenerHighPriority = $this->createMock(TestListener::class);
		$listenerAnotherHighPriority = $this->createMock(TestListener::class);

		$listenerHighPriority->expects($this->once())
											 ->method('onTestEvent')
											 ->willReturnCallback(function (TestEvent $e) use (&$callOrder) {
												 $callOrder[] = 'high';
											 });

		$listenerAnotherHighPriority->expects($this->once())
															->method('onTestEvent')
															->willReturnCallback(function (TestEvent $e) use (&$callOrder) {
																$callOrder[] = 'another_high';
															});

		$listenerNormalPriority->expects($this->once())
												 ->method('onTestEvent')
												 ->willReturnCallback(function (TestEvent $e) use (&$callOrder) {
													 $callOrder[] = 'normal';
												 });

		$listenerLowPriority->expects($this->once())
											->method('onTestEvent')
											->willReturnCallback(function (TestEvent $e) use (&$callOrder) {
												$callOrder[] = 'low';
											});


		$dispatcher->addListener(TestEvent::class, [$listenerNormalPriority, 'onTestEvent'], 0);
		$dispatcher->addListener(TestEvent::class, [$listenerHighPriority, 'onTestEvent'], 100);
		$dispatcher->addListener(TestEvent::class, [$listenerLowPriority, 'onTestEvent'], -50);
		$dispatcher->addListener(TestEvent::class, [$listenerAnotherHighPriority, 'onTestEvent'], 100);

		$dispatcher->dispatch($event);

		$this->assertEquals(['high', 'another_high', 'normal', 'low'], $callOrder, 'Listeners should be executed in correct priority order.');

	}
}

