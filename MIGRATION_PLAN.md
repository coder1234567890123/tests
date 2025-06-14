# Data Migration Plan: PHP/MySQL to Node.js/PostgreSQL

## 1. Introduction and Goals

This document outlines the plan for migrating data from the existing PHP/Symfony application (using MySQL) to the new Node.js/Express application (using PostgreSQL with Prisma ORM).

**Goals:**
-   Successfully transfer all relevant data to the new database schema.
-   Ensure data integrity and consistency post-migration.
-   Minimize downtime during the migration process.
-   Transform data where necessary to fit the new schema and application logic (e.g., password hashing, ID formats, enum values).

## 2. Pre-Migration Analysis

### 2.1. Source Database (Old System - MySQL)
-   **Users Table (`users`):**
    -   `id` (INT, PK, AI) -> Maps to `User.id` (String, UUID)
    -   `group_id` (INT, FK) -> Maps to `User.userGroupId` (String, UUID) - *Need to map old group IDs to new UserGroup UUIDs.*
    -   `team_id` (INT, FK) -> Maps to `User.teamId` (String, UUID) - *Need to map old team IDs to new Team UUIDs.*
    -   `email` (VARCHAR) -> `User.email` (String)
    -   `password` (VARCHAR, Hashed - identify old hashing method) -> `User.password` (String, bcrypt) - **Requires re-hashing or password reset strategy.**
    -   `firstName`, `lastName`, `telNumber` (for primaryMobile), `faxNumber` (likely obsolete), `mobileNumber` (for secondaryMobile), `website` (VARCHARs) -> Direct map to String fields.
    -   `enabled` (BOOLEAN/TINYINT) -> `User.enabled` (Boolean)
    -   `imageFile` (VARCHAR, path/name) -> `User.imageFile` (String) - *Schema for User does not have `imageFile`, confirm if needed or part of a profile.*
    -   `archived` (BOOLEAN/TINYINT) -> *Confirm if this field is still in new User model or handled by `enabled=false`.* (New User model does not have `archived`).
    -   `roles` (TEXT/JSON array) -> `User.roles` (Json) - *Verify format compatibility.*
    -   `token` (VARCHAR) -> `User.token` (String) - For password resets.
    -   `tokenRequested` (DATETIME) -> `User.tokenRequested` (DateTime)
    -   `company_id` (INT, FK) -> `User.companyId` (String, UUID) - *Map old company INT IDs to new Company UUIDs.*
    -   `created_at`, `updated_at` (DATETIME/TIMESTAMP) -> `User.createdAt`, `User.updatedAt` (DateTime)

-   **Companies Table (`companies`):**
    -   `id` (INT, PK, AI) -> `Company.id` (String, UUID)
    -   `created_by_id` (INT, FK to users.id) -> *New `Company` schema does not have a direct `createdById`. This needs clarification or schema adjustment if a single creator is essential. It has a `users` many-to-many relation.*
    -   `team_id` (INT, FK) -> `Company.teamId` (String, UUID)
    -   `name`, `registrationNumber`, `vatNumber`, `telNumber` (maps to `phone`), `email`, `phone` -> Direct maps.
    -   `country_id` (INT, FK) -> `Company.countryId` (String, UUID)
    -   Branding fields (`imageFile` (old schema) -> `coverLogo`?, `imageFrontPage`, `imageFooterLogo`, `coverLogo`, `pdfPassword`, `themeColor`, `themeColorSecond`, `footerLink`, `passwordSet` (obsolete?), `useDisclaimer`, `brandingType`, `disclaimer`, `allowTrait` (now on Subject), `companyTypes` (obsolete?)) -> Map to new `Company` branding fields. `allowTrait` moved to Subject.

-   **Reports Table (`reports`):**
    -   `id` (INT, PK, AI) -> `Report.id` (String, UUID)
    -   `sequence` (VARCHAR) -> `Report.sequence` (String)
    -   `assigned_to_id` (INT, FK to users.id) -> `Report.assignedToId` (String, UUID)
    -   `approved_by_id` (INT, FK to users.id) -> `Report.approvedById` (String, UUID)
    -   `requestType` (VARCHAR) -> `Report.requestType` (String)
    -   `risk`, `riskScore` -> `Report.riskScore` (Float). `risk` (textual comment?) likely maps to `riskComment`.
    -   `completedDate`, `dueDate` -> `Report.completedDate`, `Report.dueDate` (DateTime)
    -   `reportScores`, `socialMediaScores` (TEXT/JSON) -> `Report.reportScores`, `Report.socialMediaScores` (Json) - *Verify structure compatibility.*
    -   `user_id` (creator) -> `Report.createdById` (String, UUID)
    -   `company_id` -> `Report.companyId` (String, UUID)
    -   `status` (VARCHAR) -> `Report.status` (String) - *Map old status values to new if different.*
    -   `enabled` (obsolete?), `open`, `hideGeneralComments`, `hideReportScore`, `optionValue`, `riskComment`, `blobUrl`, `pdfFilename` -> Direct maps or review if needed.

-   **Subjects Table (`subjects`):**
    -   `id` (INT, PK, AI) -> `Subject.id` (String, UUID)
    -   `blobFolder` (VARCHAR/UUID) -> `Subject.blobFolder` (String, UUID)
    *   `created_by_id` (INT, FK) -> `Subject.createdById` (String, UUID)
    *   `identification`, `firstName`, `middleName`, `lastName`, `maidenName`, `nickname`, `gender`, `dateOfBirth`, `primaryEmail`, `secondaryEmail`, `primaryMobile`, `secondaryMobile` -> Direct maps.
    *   `handles`, `educationInstitutes` (TEXT/JSON array) -> `Subject.handles`, `Subject.educationInstitutes` (Json).
    *   `address` fields (street, suburb, city, postal_code, province - old schema) -> New `Address` model linked via `Subject.addressId`. *Requires creating Address records and linking.*
    *   `country_id`, `company_id` (INT FKs) -> `Subject.countryId`, `Subject.companyId` (String UUIDs).
    *   `enabled` (obsolete?), `rushReport`, `allowTrait`, `imageFile`, `status`, `reportType` -> Direct maps.

-   **Other Tables to Map (High-Level):**
    *   `accounts` (old) -> `Accounts` (new)
    *   `answers` (old) -> `Answer` (new) - Link to new Report UUIDs, Question UUIDs, User UUIDs.
    *   `comments` (old) -> `Comment` (new) - Link to new Report UUIDs, User UUIDs.
    *   `company_product` (old) -> `CompanyProduct` (new)
    *   `countries` (old) -> `Country` (new) - Map INT IDs to new UUIDs.
    *   `default_branding` (old) -> `DefaultBranding` (new)
    *   `email_tracker` (old) -> `EmailTracker` (new)
    *   `employments` (old) -> `Employment` (new) - Link to Subject UUIDs, handle Address relation.
    *   `global_weights` (old) -> `GlobalWeights` (new)
    *   `groups` (old) -> `UserGroup` (new) - Map user relations.
    *   `identity_confirmations` (old) -> `IdentityConfirm` (new)
    *   `message_queues` (old) -> `MessageQueue` (new)
    *   `message_systems` (old) -> `MessageSystem` (new)
    *   `phrases` (old) -> `Phrase` (new)
    *   `products` (old) -> `Product` (new)
    *   `subject_profiles` (old `profiles`) -> `Profile` (new) - Link to Subject UUIDs.
    *   `proofs` (old) -> `Proof` (new) - Link to Answer UUIDs.
    *   `proofstorages` (old) -> `ProofStorage` (new)
    *   `provinces` (old) -> `Province` (new)
    *   `qualifications` (old) -> `Qualification` (new) - Link to Subject UUIDs.
    *   `questions` (old) -> `Question` (new) - Map INT IDs to new UUIDs.
    *   `report_sections` (old) -> `ReportSection` (new)
    *   `report_time_frames` (old) -> `ReportTimeFrame` (new)
    *   `roles` (old) -> `Role` (new) - Link to RoleGroup UUIDs.
    *   `role_groups` (old) -> `RoleGroup` (new)
    *   `system_config` (old) -> `SystemConfig` (new)
    *   `teams` (old) -> `Team` (new) - Link to User UUID for `teamLeaderId`.
    *   `user_tracking` (old) -> `UserTracking` (new)

### 2.2. Target Database (New System - PostgreSQL with Prisma)
-   Schema defined in `express-prisma-api/prisma/schema.prisma`.
-   Primary Keys are UUIDs generated by Prisma (`@default(uuid())`).
-   Relationships are explicitly defined with `@relation`.

## 3. Data Transformation Requirements

-   **ID Mapping (INT to UUID):** This is the most critical transformation.
    -   Strategy: For each table migrated (e.g., `users`, `companies`, `reports`), generate a new UUID for each row. Store a mapping `(old_mysql_int_id, new_postgresql_uuid)` in temporary lookup tables or in-memory maps during the script's execution for that entity type. When migrating child tables that have foreign keys to this entity, use the lookup map to find the new parent UUID based on the old integer FK.
-   **Password Migration:**
    -   **Strategy:** Do not migrate old password hashes. All migrated users will be required to reset their passwords upon their first login attempt to the new system.
    -   Implementation: During User data migration, populate the new `User.password` field with a placeholder hash that is known to be invalid (e.g., hash of a very long random string, or a specific flag string). Alternatively, add a boolean field like `passwordResetRequired` to the User model, set it to true for all migrated users. The login logic in the new application will check for this and redirect to a password reset flow.
-   **JSON Data (`User.roles`, `Subject.handles`, `Question.reportTypes`, etc.):**
    -   Old system might store these as serialized PHP arrays or comma-separated strings.
    -   Transform these into valid JSON arrays of strings (e.g., `["ROLE_USER", "ROLE_ADMIN"]`) for Prisma's `Json` type. Handle empty or NULL values appropriately (e.g., map to `[]` or `null`).
-   **Timestamps (`createdAt`, `updatedAt`):**
    -   Ensure correct mapping from MySQL `DATETIME` or `TIMESTAMP` to PostgreSQL `DateTime` via Prisma. Verify timezone consistency; PostgreSQL typically stores `timestamp with time zone` as UTC.
-   **Status/Enum Values:**
    *   Map old string/integer status values from tables like `reports`, `subjects` to the new string constants defined in services (e.g., `REPORT_STATUSES` in `workflowService.js`). Create explicit mapping rules if values differ.
-   **File Paths (`Proof.filePath`, `Company` branding image fields):**
    *   The new system expects relative paths (e.g., `proofs/filename.ext` or `company-images/[companyId]/logo.png`).
    *   Old system paths need transformation. The actual file movement is a separate step but path data must be consistent.
-   **Embedded Addresses (e.g., old `Subject` address fields):**
    *   Extract address data from source tables (`subjects`, `employments`).
    *   For each unique address, create a new record in the `Address` table (generating a new UUID for it).
    *   Store a mapping from `(old_subject_id, new_address_uuid)` or `(old_employment_id, new_address_uuid)`.
    *   Populate `Subject.addressId` or `Employment.addressId` with the corresponding new Address UUID. Handle cases where multiple old entities might share the exact same address data (create one Address record, link multiple times).

## 4. Migration Strategy & Execution

1.  **Preparation:**
    *   **Full Backup:** Backup old MySQL database.
    *   **New DB Setup:** PostgreSQL database created. Run `npx prisma migrate deploy` in `express-prisma-api/` to set up the schema.
    *   **Migration Scripts:** Develop Node.js scripts using Prisma Client for PostgreSQL writes, and a MySQL connector (e.g., `mysql2`) for MySQL reads.
    *   **ID Mapping Storage:** Use temporary tables in PostgreSQL or in-memory maps within scripts for `(old_int_id, new_uuid)` mappings.
    *   **Staging Environment:** Test migration scripts thoroughly in a staging environment.

2.  **Downtime:** Schedule downtime window.

3.  **Execution Order (Example - actual order depends on FK constraints):**
    1.  Stop Old Application.
    2.  (Optional) Final Backup.
    3.  **Independent Entities:** Migrate tables with no or few FK dependencies first, generating UUIDs and ID maps:
        *   `Country`, `Province`, `RoleGroup`, `Role`, `DefaultBranding`, `Product`, `ReportSection`, `ReportTimeFrame`, `Phrase` (if no `createdById` initially), `SystemConfig`, `GlobalWeights`.
    4.  **User-related Entities:**
        *   `UserGroup` (from old `groups`). Store `(old_group_id, new_usergroup_uuid)`.
        *   `Team`. Store `(old_team_id, new_team_uuid)`.
        *   `Company`. Use country map. Store `(old_company_id, new_company_uuid)`. Link to team using team map.
        *   `User`. Use group, team, company maps. Set password for reset. Store `(old_user_id, new_user_uuid)`. Update `Team.teamLeaderId` using user map.
    5.  **Subject-related Core Entities:**
        *   `Address` (from old subject/employment addresses). Store `(old_entity_id_plus_type, new_address_uuid)`.
        *   `Subject`. Use user, company, country, address maps. Store `(old_subject_id, new_subject_uuid)`.
    6.  **Report-related Core Entities:**
        *   `Question`. Store `(old_question_id, new_question_uuid)`.
        *   `Report`. Use subject, user, company maps. Store `(old_report_id, new_report_uuid)`.
    7.  **Remaining Dependent Entities:** Migrate in order of dependency, using stored ID maps:
        *   `Accounts` (Company)
        *   `CompanyProduct` (Company, Product)
        *   `Profile` (Subject)
        *   `Qualification` (Subject)
        *   `Employment` (Subject, Address)
        *   `Answer` (Report, Question, User, Subject)
        *   `Proof` (Answer) - handle `proofStorageId` if `ProofStorage` is migrated first or concurrently.
        *   `Comment` (Report, User)
        *   `EmailTracker`, `IdentityConfirm`, `MessageQueue`, `MessageSystem`, `ProofStorage`, `UserTracking`.
    8.  **File Migration:** Separate script/process to move files to new structure based on migrated path data.
    9.  **Data Validation:** Row counts, spot checks, FK integrity checks (e.g., count null FKs where not expected).
    10. **Post-Migration Tasks:** Database cleanup, indexing.

## 5. Key Challenges and Risks

-   **ID Mapping Integrity:** Ensuring all old INT IDs are correctly mapped to new UUIDs and used for FKs.
-   **Password Reset:** Communicating the mandatory password reset to users.
-   **Data Transformation Accuracy:** Correctly parsing JSON, mapping statuses, transforming dates/times.
-   **File Migration:** Volume and path mapping for physical files.
-   **Downtime Duration:** Script efficiency for large datasets.
-   **Testing:** Thorough testing of migrated data in the new application.
-   **Rollback Complexity:** Restoring the old system if major issues arise.

## 6. Rollback Plan

-   If critical issues found:
    1.  Restore old MySQL database.
    2.  Re-point application/DNS to the old system.
    3.  Analyze migration logs and scripts.
    4.  Reschedule migration.

This plan provides a structured approach. Each data migration script for each table will need careful development and unit testing.
```
