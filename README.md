# Financial Events Service

## Description

A Symfony-based microservice for processing financial events.

## Getting Started

### Installing

1. **Clone the repository:**
    ```bash
    git clone https://github.com/erikonprime/financial-events-service.git
    cd financial-events-service
    ```
   
2. **Build and start the containers:**
    ```bash
    docker compose build --no-cache
    docker compose up -d --force-recreate
    ```

3. **Install Composer dependencies:**
    ```bash
    docker exec -it financial-events-service-php composer install
    ```
   
4. **Execute migrations:**
    ```bash
    docker exec -it financial-events-service-php php bin/console doctrine:migrations:migrate
    ```
   
5. **Run tests:**
   ```bash
   docker exec -it financial-events-service-php php bin/phpunit
   ```
   
6. **Your API is available at:**
*   http://localhost:8081
   
7. **Api Documentation:**
* http://localhost:8081/api/doc
   
## API Endpoints

### 1. Process Event
Processes a new financial event and records the corresponding accounting transactions.

- **URL**: `/events`
- **Method**: `POST`
- **Content-Type**: `application/json`
- **Payload Example**:
```json
{
  "event_id": "evt_12345",
  "type": "payment_received",
  "amount": 1500.00,
  "currency": "USD",
  "timestamp": "2026-03-21T19:00:00Z"
}
```
- **Supported Event Types**:
    - `payment_received`
    - `payment_sent`
    - `fee_charged`

### 2. Get Account Balance
Retrieves the current balance for a specific account.

- **URL**: `/accounts/{account}/balance`
- **Method**: `GET`
- **Example Response**:
```json
{
  "account": "system_cash_account",
  "balance": 1500.00
}
```

### 3. Get Account Transactions
Retrieves all recorded transactions for a specific account.

- **URL**: `/accounts/{account}/transactions`
- **Method**: `GET`
- **Example Response**:
```json
{
    "account": "system_cash_account",
    "transactions": [
        {
            "account": "system_cash_account",
            "direction": "credit",
            "amount": 1500.00,
            "currency": "USD",
            "timestamp": "2026-03-21T19:00:00Z"
        }
    ]
}
```
