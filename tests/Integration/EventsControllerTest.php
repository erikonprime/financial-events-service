<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\AccountingTransaction;
use App\Entity\EventProcessed;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class EventsControllerTest extends WebTestCase
{

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    #[DataProvider('dataTestCreatesTransactions')]
    public function testCreatesTransactions(
        array $payload,
        string $eventId,
        string $debitAccount,
        string $creditAccount,
    ): void {
        $this->client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertTransactions($eventId, $debitAccount, $creditAccount);
    }

    public static function dataTestCreatesTransactions(): array
    {
        return [
            [
                [
                    'event_id' => 'evt_test_001',
                    'type' => 'payment_received',
                    'amount' => 100.00,
                    'currency' => 'EUR',
                    'timestamp' => '2026-01-01T00:00:00Z',
                ],
                'evt_test_001',
                'user_account',
                'system_cash_account',
            ],
            [
                [
                    'event_id' => 'evt_test_002',
                    'type' => 'payment_sent',
                    'amount' => 122.22,
                    'currency' => 'USD',
                    'timestamp' => '2026-01-01T00:00:00Z',
                ],
                'evt_test_002',
                'system_cash_account',
                'user_account',
            ],
            [
                [
                    'event_id' => 'evt_test_003',
                    'type' => 'fee_charged',
                    'amount' => 133.33,
                    'currency' => 'GPB',
                    'timestamp' => '2026-01-01T00:00:00Z',
                ],
                'evt_test_003',
                'user_account',
                'fee_account',
            ],
        ];
    }

    public static function tearDownAfterClass(): void
    {
        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement($platform->getTruncateTableSQL('accounting_transaction', true));
        $connection->executeStatement($platform->getTruncateTableSQL('event_processed', true));
        $entityManager->close();

        parent::tearDownAfterClass();
    }

    private function assertTransactions(string $eventId, string $debitAccount, string $creditAccount): void
    {
        $em = static::getContainer()->get('doctrine')->getManager();
        $eventProcessed = $em
            ->getRepository(EventProcessed::class)
            ->findOneBy(['eventId' => $eventId]);

        self::assertNotNull($eventProcessed, "EventProcessed entity not found for ID: $eventId");

        $repo = $em->getRepository(AccountingTransaction::class);
        $transactions = $repo->findBy(['event' => $eventProcessed], ['direction' => 'ASC']);

        self::assertCount(2, $transactions);

        /** @var AccountingTransaction $debitAccountTransaction */
        /** @var AccountingTransaction $creditAccountTransaction */
        [$creditAccountTransaction, $debitAccountTransaction] = $transactions;

        //credit
        self::assertSame($creditAccount, $creditAccountTransaction->getAccount()->value);
        //debit
        self::assertSame($debitAccount, $debitAccountTransaction->getAccount()->value);
    }

    public function testInvalidJson(): void
    {
        $this->client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{ qwerty }',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testInvalidPayload(): void
    {
        $payload = [
            'event_id' => 'event_1',
            'type' => 'payment_received',
            'amount' => -10.0, // Negative amount
            'currency' => 'EUR',
            'timestamp' => '2026-01-01T00:00:00Z',
        ];

        $this->client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUnknownType(): void
    {
        $payload = [
            'event_id' => 'event_1',
            'type' => 'unknown_event_type', // Unknown type
            'amount' => 100.0,
            'currency' => 'EUR',
            'timestamp' => '2026-01-01T00:00:00Z',
        ];

        $this->client->request(
            'POST',
            '/events',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
