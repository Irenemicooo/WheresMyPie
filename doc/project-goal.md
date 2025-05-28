# Project Goal: Where’s My Pie?

## Overview

**Where’s My Pie?** is a community-based lost and found web application that allows users to report, search for, and claim lost items. The goal is to provide a smooth and secure way for people to retrieve their missing belongings.

**Group: TreeNoPie**


## High-Level Functionalities

- Users must register an account and provide contact details (name, phone number, etc.)
- Users can:
  - Submit a "Found Item" form with details (date, location, category, description, image).
  - View the full list of reported lost items.
  - Search for items by keywords or filters (category, date, location).
  - Click on items to fill out a claim form with details and evidence.
  - View their account and update contact information.
- Finders can:
  - Review submitted claim forms for the items they uploaded.
  - Approve a claim if it matches their record.
  - See the claimant’s contact information only after approval.
- Chat system:
  - After a claim is approved, the finder and claimant can chat privately.
- No administrator role is included; all approvals are handled by users themselves.
- Homepage includes a notice board and quick access to all features.



## Scenario 1: A User Submits a Found Item

**User**: A user who found a lost item on campus.

**User Action**:
1. Logs into the website.
2. Clicks on "Submit Found Item".
3. Fills in the form with item details (category, date, location, description).
4. Uploads a photo.
5. Submits the form.

**System Process**:
- PHP validates form fields.
- Stores uploaded image to the server.
- Inserts item data into the MariaDB database.

**System Response**:
> “Thank you! Your found item has been listed for others to see.”


## Scenario 2: A User Searches and Claims an Item

**User**: A user who lost an item.

**User Action**:
1. Logs into the website.
2. Clicks on "Search for Items".
3. Filters items by category and location.
4. Finds an item that looks familiar.
5. Clicks "Claim" next to the item.
6. Fills in claim form with contact info and proof (text or image).
7. Submits the claim.

**System Process**:
- PHP validates inputs.
- Claim is stored in MariaDB, marked as "pending".
- The item’s uploader (finder) is notified and sees the claim request.

**Finder Action**:
- Logs into their account and checks the claim.
- Approves the matching claim.
- Both users are shown each other's contact info and chat becomes available.

**System Response**:
> “Your claim has been sent to the item uploader. Please wait for approval.”
> (After approval: “Claim approved. You may now contact each other.”)



## Notes

- Users can update account info (username, password, phone number).
- Each item can receive multiple claims, but only one can be approved.
- Chat is only available after a claim is approved.
- Items may remain in the system for a certain time limit; this behavior may be expanded in the future.