# Postcode Lookup API Documentation

## Overview

The Postcode Lookup API provides services for looking up UK postcodes, finding postcodes within a radius, and calculating distances between postcodes. All endpoints are protected by Laravel Sanctum authentication.

## Authentication

All API requests require a valid Bearer token in the Authorization header:

```
Authorization: Bearer your_api_token_here
```

## Endpoints

### 1. Postcode Lookup

Retrieves details for a specific postcode.

**Endpoint:** `GET /api/postcode/lookup`

**Parameters:**

-   `postcode` (required): String, max 8 characters

**Example Request:**

```bash
curl -X GET \
  'https://api.example.com/api/postcode/lookup?postcode=SW1A1AA' \
  -H 'Authorization: Bearer your_api_token_here'
```

**Success Response:**

```json
{
    "success": true,
    "data": {
        // Postcode details
    }
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "Postcode not found"
}
```

### 2. Radius Search

Finds postcodes within a specified radius of a given postcode.

**Endpoint:** `GET /api/postcode/radius`

**Parameters:**

-   `postcode` (required): String, max 8 characters
-   `radius` (required): Numeric, between 1 and 50000 meters

**Example Request:**

```bash
curl -X GET \
  'https://api.example.com/api/postcode/radius?postcode=SW1A1AA&radius=1000' \
  -H 'Authorization: Bearer your_api_token_here'
```

**Success Response:**

```json
{
    "success": true,
    "data": [
        // Array of postcodes within radius
    ]
}
```

### 3. Distance Calculation

Calculates the distance between two postcodes.

**Endpoint:** `GET /api/postcode/distance`

**Parameters:**

-   `from` (required): String, max 8 characters
-   `to` (required): String, max 8 characters

**Example Request:**

```bash
curl -X GET \
  'https://api.example.com/api/postcode/distance?from=SW1A1AA&to=W1A1AA' \
  -H 'Authorization: Bearer your_api_token_here'
```

**Success Response:**

```json
{
    "success": true,
    "data": {
        "distance": 1234.56,
        "unit": "meters"
    }
}
```

**Error Response (404):**

```json
{
    "success": false,
    "message": "One or both postcodes not found"
}
```

## Error Codes

| Status Code | Description                             |
| ----------- | --------------------------------------- |
| 200         | Successful request                      |
| 401         | Unauthorized - Invalid or missing token |
| 404         | Not found - Postcode(s) not found       |
| 422         | Validation error - Invalid parameters   |
| 429         | Too many requests - Rate limit exceeded |

## Caching

All responses are cached for 24 hours (1440 minutes) to improve performance. The cache is automatically invalidated and refreshed when making new requests.

## Rate Limiting

The API implements Laravel's rate limiting. Default limits and specific quotas will be provided by your API administrator.

## Code Examples

### PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->get('https://api.example.com/api/postcode/lookup', [
    'headers' => [
        'Authorization' => 'Bearer your_api_token_here',
        'Accept' => 'application/json',
    ],
    'query' => [
        'postcode' => 'SW1A1AA'
    ]
]);

$data = json_decode($response->getBody(), true);
```

### JavaScript (Fetch)

```javascript
const fetchPostcode = async (postcode) => {
    const response = await fetch(
        `https://api.example.com/api/postcode/lookup?postcode=${postcode}`,
        {
            method: "GET",
            headers: {
                Authorization: "Bearer your_api_token_here",
                Accept: "application/json",
            },
        }
    );

    return await response.json();
};
```

### Python (Requests)

```python
import requests

headers = {
    'Authorization': 'Bearer your_api_token_here',
    'Accept': 'application/json'
}

response = requests.get(
    'https://api.example.com/api/postcode/lookup',
    headers=headers,
    params={'postcode': 'SW1A1AA'}
)

data = response.json()
```

## Token Management

### For Administrators

#### Generate New Token

```bash
php artisan sanctum:create-token "Company Name" "company@example.com"
```

#### List Tokens

```bash
# List all tokens
php artisan sanctum:list-tokens

# List tokens for specific user
php artisan sanctum:list-tokens company@example.com
```

#### Revoke Tokens

```bash
# Revoke all tokens for a user
php artisan sanctum:revoke-token --user=company@example.com --all

# Revoke specific token
php artisan sanctum:revoke-token --token=123
```

## Best Practices

1. **Security**

    - Store API tokens securely in environment variables
    - Never expose tokens in client-side code
    - Implement proper error handling
    - Use HTTPS for all API calls

2. **Performance**

    - Take advantage of the built-in caching
    - Implement retry logic with exponential backoff
    - Monitor rate limits

3. **Error Handling**
    - Always check for error responses
    - Implement proper logging
    - Handle rate limiting gracefully

## Support

For technical support or to report security issues, please contact the API administrator.
