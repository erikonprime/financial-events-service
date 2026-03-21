<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;

class AccountsControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    #[DataProvider('dataTestGetBalance')]
    public function testGetBalance(string $account, float $balance): void
    {
        $url = sprintf('/accounts/%s/balance', $account);
        $this->client->request('GET', $url);
        $response = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertEquals($account, $response['account'] ?? '');
        self::assertEqualsWithDelta($balance, $response['balance'], 0.01);
    }

    public static function dataTestGetBalance(): array
    {
        return [
            [
                'user_account',
                -288.27,
            ],
            [
                'system_cash_account',
                32.01,
            ],
            [
                'fee_account',
                256.26,
            ],
        ];
    }

    public static function setUpBeforeClass(): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine')->getConnection();

        static::insertPaymentReceived($connection);
        static::insertPaymentSent($connection);
        static::insertFeeCharged($connection);
        parent::tearDownAfterClass();
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

    private static function insertPaymentReceived(Connection $connection): void
    {
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_001',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'debit',
            'amount' => '100.00',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'system_cash_account',
            'direction' => 'credit',
            'amount' => '100.00',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    private static function insertPaymentSent(Connection $connection): void
    {
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_002',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'system_cash_account',
            'direction' => 'debit',
            'amount' => '67.99',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'credit',
            'amount' => '67.99',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    private static function insertFeeCharged(Connection $connection): void
    {
        $connection->insert('event_processed', [
            'event_id' => 'evt_test_003',
            'payload' => json_encode(['type' => 'manual_insert']),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $eventId = $connection->lastInsertId();

        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'user_account',
            'direction' => 'debit',
            'amount' => '256.26',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
        $connection->insert('accounting_transaction', [
            'event_id' => $eventId,
            'account' => 'fee_account',
            'direction' => 'credit',
            'amount' => '256.26',
            'currency' => 'EUR',
            'event_timestamp' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }
}
