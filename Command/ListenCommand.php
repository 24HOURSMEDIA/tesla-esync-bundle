<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 13/07/14
 * Time: 00:03
 */

namespace Tesla\EsyncBundle\Command;


use Symfony\Bridge\Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tesla\Esync\Aws\SqsIncomingMessageEvent;
use Tesla\Esync\Event\IncomingMessageEvent;
use Tesla\EsyncBundle\Event\EsyncEvents;
use Tesla\EsyncBundle\Event\UpdateEvent;
use Tesla\EsyncBundle\Event\CreateEvent;
use Tesla\EsyncBundle\Event\DeleteEvent;
use Tesla\EsyncBundle\Event\WorkflowEvent;
use Tesla\Esync\Event\EsyncBaseEvents;

class ListenCommand extends ContainerAwareCommand
{


    protected function configure()
    {
        $this
            ->setName('esync:listen')
            ->setDescription('Listen to the queue')/*
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Who do you want to greet?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )
        */
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /* @var $logger \Psr\Log\LoggerInterface */
        $logger = $this->getContainer()->get('tesla_esync.logger');

        $logger->info('start listening to events');

        $output->writeln(date('Y-m-d H:i:s') . ' Listening to the queue');
        $listener = $this->getContainer()->get('tesla_esync.command_receiver');
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');

        // uow events
        $eventDispatcher->addListener(
            EsyncEvents::START_UOW,
            function() use ($output) {
                $output->writeln('[start uow]');
            }
        );

        $eventDispatcher->addListener(
            EsyncEvents::END_UOW,
            function() use ($output) {
                $output->writeln('[end uow]');
            }
        );

        // feedback listener
        $eventDispatcher->addListener(
            EsyncBaseEvents::MESSAGE_RECEIVE,
            function (IncomingMessageEvent $event) use ($output) {
                $m = $event->getMessage();
                $output->writeln('- Found <info>' . $m->getType() . '</info> event for object <info>' . $m->getClass() . ':' . $m->getId() . '</info>');
            },
            -1
        );
        // processing listener
        $eventDispatcher->addListener(
            EsyncBaseEvents::MESSAGE_RECEIVE,
            function (IncomingMessageEvent $event) use ($output, $eventDispatcher, $logger) {
                $m = $event->getMessage();
                $attrs = $m->getData();

                $logger->info('processing ' . $m->getType() . ' event for ' . $m->getClass() . ':' . $m->getId() . ' originating from system ' . @$attrs['originating_system']);
                switch ($m->getType()) {
                    case 'update':
                        $eventDispatcher->dispatch(EsyncEvents::UPDATE, new UpdateEvent($m));
                        break;
                    case 'create':
                        $eventDispatcher->dispatch(EsyncEvents::CREATE, new CreateEvent($m));
                        break;
                    case 'delete':
                        $eventDispatcher->dispatch(EsyncEvents::DELETE, new DeleteEvent($m));
                        break;
                    default:
                        $event->stopPropagation();
                }
                $output->writeln('  processed.');
            },
            -2
        );

        $eventDispatcher->dispatch(EsyncEvents::START_UOW, new WorkflowEvent(),0);
        $listener->listen(10);
        $eventDispatcher->dispatch(EsyncEvents::END_UOW, new WorkflowEvent(),0);

        $output->writeln(date('Y-m-d H:i:s') . ' Done');
        $logger->info('end listening to events');
    }
} 