<p align="center">TELEPHONY-API</p>

## About Telephony-API

This is a Laravel backend web application exposing APIs for the telephony-core phone and also to the continulink
application. Core entities include:

- Profile: This is the project that intends to use the telephony
- User: This is the caregiver or the clinician
- Patient: This is the entity receiving the care
- Agency: This is the organization that manages a patient and her caregiver.
- Visit: This is a scheduled visitation of a caregiver to a patient
- Questions: These are generic list of possible tasks a caregiver can perform.
- QuestionSet: This are set of selected/specific tasks a caregiver is expected to perform on a specific patient.

## API Authentication
- Provide auth-user: xxxx and auth-key: xxxx in the headers of each request as this helps identify a profile. 