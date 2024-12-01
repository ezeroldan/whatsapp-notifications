<?php

namespace App\Command;

use App\Entity\Message;
use App\Service\WhatsAppSenderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class NotifyCommand extends Command
{
    private EntityManagerInterface $em;
    private WhatsAppSenderService $sender;

    public function __construct(
        EntityManagerInterface $em,
        WhatsAppSenderService $sender
    ) {
        parent::__construct(null);

        $this->em = $em;
        $this->sender = $sender;
    }

    protected function configure(): void
    {
        $this
            ->setName('notify')
            ->setDescription('Notify the message via whatsapp')
            ->addArgument('limit', InputArgument::OPTIONAL, 'Max limit of messages', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Recover the first 10 pending menssage');

        /** @var Message[] */
        $messages = $this->em->getRepository(Message::class)
            ->findBy(['status' => Message::STATUS_PENDING], null, $input->getArgument('limit'));

        if (!$messages) {
            $output->writeln('No pending messages to send');
            return Command::SUCCESS;
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Phone', 'Message', 'Status']);

        foreach ($messages as $message) {
            $this->sender->send($message);
            $table->addRow([
                $message->getId(),
                $message->getPhone(),
                $message->getMessage(),
                $message->getStatus()
            ]);
        }

        $table->render();

        $this->em->flush();

        return Command::SUCCESS;
    }
}
