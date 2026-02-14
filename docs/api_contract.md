# API Contract

All API endpoints must return:

`App\Domain\DTO\ApiResponse`

---

## Envelope Structure

    {
      "ok": true,
      "result": {},
      "error_code": null,
      "error_message": null,
      "status_code": 200,
      "errors": null
    }

---

## Field Definitions

- ok — boolean success flag
- result — payload for successful requests
- error_code — string from ResponseErrorCode enum
- error_message — human-readable message
- status_code — HTTP code duplicated in body
- errors — field-level validation errors

---

## Error Codes

Defined in:

`App\Domain\Enum\ResponseErrorCode`

Required minimum set:

- validation_failed
- unauthorized
- forbidden
- not_found
- internal_error

All new stable error states must introduce a new enum case.
