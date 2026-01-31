# Mobile App API Guide

This document outlines the available API endpoints for the Komiktap mobile application.

**Base URL**: `https://your-domain.com/api`

## 1. System Configuration
### Get App Config
Retrieves dynamic application configuration (feature flags, ad settings, etc.).
- **Endpoint**: `GET /config`
- **Auth**: Public or Optional Bearer Token
- **Response**: JSON (Configuration object)

---

## 2. User Profile
### Get Current User
Retrieves the currently authenticated user's profile.
- **Endpoint**: `GET /user`
- **Auth**: **Required** (Bearer Token)
- **Response (Success)**:
  ```json
  {
    "app": "Kuron",
    "version": "1.0.0",
    "status": "success",
    "data": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    }
  }
  ```

---

## 3. Plans & Payments
Retrieves available subscription plans.
- **Endpoint**: `GET /plans`
- **Response**: List of plans

### Get FAQs
Retrieves Frequently Asked Questions.
- **Endpoint**: `GET /faqs`
- **Response**: List of FAQ items

### Get Payment Methods
Retrieves active payment gateways/methods.
- **Endpoint**: `GET /payment-methods`
- **Response**: List of payment methods

---

## 2. Licensing & Payments
### Check License Validity
Verifies if a license key is valid and active.
- **Endpoint**: `POST /check-license`
- **Request Body**:
  ```json
  {
    "license_key": "XXXX-XXXX-XXXX-XXXX",
    "device_id": "unique-device-id"
  }
  ```
- **Response (Success)**:
  ```json
  {
    "app": "Kuron",
    "version": "1.0.0",
    "status": "success",
    "data": {
      "status": "valid",
      "expires_at": "2026-12-31 23:59:59"
    }
  }
  ```

### Checkout / Purchase
Initiates a transaction for a plan.
- **Endpoint**: `POST /checkout`
- **Request Body**:
  ```json
  {
    "plan_id": 1,
    "payment_method": "qris",
    "customer_phone": "08123456789"
  }
  ```
- **Response (Success)**:
  ```json
  {
    "app": "Kuron",
    "version": "1.0.0",
    "status": "success",
    "data": {
       "transaction_id": 105,
       "transaction_code": "TRX-2026-XXX",
       "message": "Order received successfully!"
    }
  }
  ```

---

## 3. Error Reporting (New)
Submits a crash report or error log from the mobile application.

### Submit Error Report
- **Endpoint**: `POST /error-report`
- **Auth**: Optional Bearer Token (if user is logged in, report will be linked to user)
- **Content-Type**: `application/json`

#### Request Body
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `error_message` | string | **Yes** | Short description of the error |
| `stack_trace` | string | No | Full stack trace or detailed log |
| `device_info` | string | No | Device model, OS version (e.g., "Samsung S24, Android 14") |
| `app_version` | string | No | App version code (e.g., "1.2.0") |

#### Example Request
```json
{
  "error_message": "NullPointerException in MainActivity",
  "stack_trace": "java.lang.NullPointerException: Attempt to invoke virtual method on a null object reference\n\tat com.komiktap.app.MainActivity.onCreate(MainActivity.kt:42)",
  "device_info": "Pixel 7 Pro, Android 14",
  "app_version": "1.0.5"
}
```

#### Example Response (Success - 201 Created)
```json
{
  "app": "Kuron",
  "version": "1.0.0",
  "status": "success",
  "data": {
    "message": "Error report submitted successfully",
    "report_id": 15
  }
}
```

#### Example Response (Validation Error - 422 Unprocessable Entity)
```json
{
  "app": "Kuron",
  "version": "1.0.0",
  "status": "failed",
  "message": "Validation error",
  "data": {
    "error_message": [
      "The error message field is required."
    ]
  }
}
```
