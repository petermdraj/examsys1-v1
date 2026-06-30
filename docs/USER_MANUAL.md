# ExamSys User Manual

**ExamSys** is an institutional exam and quiz platform. Administrators manage users and platform settings, lecturers create and assign exams, and students take exams through a dedicated portal.

This manual covers all three roles: **Administrator**, **Lecturer**, and **Student**.

---

## Table of Contents

1. [Getting Started](#1-getting-started)
2. [Administrator Guide](#2-administrator-guide)
3. [Lecturer Guide](#3-lecturer-guide)
4. [Student Guide](#4-student-guide)
5. [Shared Concepts](#5-shared-concepts)
6. [Appendix](#6-appendix)

---

## 1. Getting Started

### 1.1 What ExamSys Does

ExamSys lets institutions:

- Organize students into **batches** (class groups)
- Let **lecturers** build exams with manual questions, a question bank, or AI generation
- **Assign** exams to individual students or entire batches
- Run timed, proctored-style exams with automatic scoring
- Issue **PDF certificates** with public verification
- Monitor live exams and export grade reports

There is no public student registration. An administrator creates every account.

### 1.2 User Roles

| Role | Panel / Portal | Primary responsibilities |
|------|----------------|--------------------------|
| **Super Admin** | `/admin` (+ `/lecturer`) | Full platform control, settings, system health, impersonation |
| **Admin** | `/admin` | User management, monitoring, reports (no platform settings) |
| **Lecturer** | `/lecturer` | Create exams, assign to students, view reports |
| **Student** | Public site (`/`, `/my/*`) | Discover assigned exams, take exams, view results |

### 1.3 Logging In

| Role | Login URL | After login |
|------|-----------|-------------|
| Super Admin / Admin | `/admin/login` | Admin Command Center (`/admin`) |
| Lecturer | `/lecturer/login` | Lecturer Dashboard (`/lecturer`) |
| Student | `/login` | Student Dashboard (`/my/dashboard`) |

**Google sign-in** is available for existing accounts only. If your email is not registered, you will see a message that accounts must be created by an administrator.

**Email verification (OTP):** When enabled in platform settings, students must verify their email with a 6-digit code before accessing exams or the student portal.

**Password reset:** Use **Forgot password** on the login page (`/forgot-password`).

### 1.4 Language

If multiple languages are enabled, use the language switcher in the top navigation (admin, lecturer, and public site each have their own switcher visibility setting).

---

## 2. Administrator Guide

**Access:** `/admin`  
**Roles:** `super_admin` and `admin`

The admin panel is organized into sidebar groups: **Management**, **Academics**, **Configuration**, plus **Live Monitoring** at the top level.

---

### 2.1 Command Center (Dashboard)

**URL:** `/admin`

The Command Center is your operational home screen. It shows:

- **KPI cards** — live exam sessions, registered students, pass rate, completions today
- **Active exam hero** — highlights the exam with the most activity right now
- **Exam status breakdown** — published, scheduled, draft, and archived counts
- **Performance chart** — attempt trends over time
- **Risk alerts** — sessions flagged for unusual behavior
- **Upcoming exams** — exams scheduled to open soon
- **Top lecturers** — lecturers with the most exam activity

Several widgets refresh automatically every 30 seconds. Use the KPI link to jump directly to **Live Monitoring**.

---

### 2.2 Live Monitoring

**URL:** `/admin/live-monitoring`  
**Permission:** Admin and Super Admin

Monitor every in-progress exam attempt in real time.

**What you can see:**

- Student name and exam title
- Time remaining, progress, risk level
- Session status (in progress, paused, etc.)

**Filters:**

- Exam
- Risk level
- Status
- Student search

**Actions on a session:**

| Action | Effect |
|--------|--------|
| **Pause** | Freezes the student's exam screen with an overlay until you resume |
| **Resume** | Removes the pause overlay so the student can continue |
| **Force Submit** | Immediately ends the attempt and scores it |
| **Reopen** | Allows a terminated attempt to be continued (when applicable) |

Use Live Monitoring during scheduled class exams to intervene if a student loses connectivity or needs assistance.

---

### 2.3 Admins & Lecturers (Staff Management)

**URL:** `/admin/staff`

Create and manage platform staff accounts.

**Creating staff:**

1. Click **Create**
2. Enter name, email, and password
3. Choose role: **Super Admin**, **Admin**, or **Lecturer**
4. Optionally set phone, country, and AI credits (for AI question generation)
5. Save

**Row actions:**

- **Login as** (Super Admin only) — impersonate the user to troubleshoot their experience
- **Suspend / Activate** — disable or re-enable the account
- **Grant AI credits** — add credits for AI question generation

**Bulk actions:** Activate, suspend, or delete multiple staff at once.

**Restrictions:**

- Regular admins cannot edit or delete Super Admin accounts
- Super Admin accounts cannot be impersonated
- Super Admin role can only be assigned by a Super Admin

---

### 2.4 Students

**URL:** `/admin/students`

Manage student accounts. Every student must belong to a **Student Batch**.

**Creating a student:**

1. Click **Create**
2. Enter name, email, and password
3. Select a **Student Batch**
4. Save

**When you change a student's batch**, their quiz enrollments are synchronized to match the new batch's assignments.

**Row actions:**

- **Login as** (Super Admin only) — view the site as that student
- **Suspend / Activate**
- **Bulk** activate, suspend, or delete

---

### 2.5 Student Batches

**URL:** `/admin/student-batches`

Batches group students for assignment (e.g., "CS101 — Fall 2026", "Batch A").

**Creating a batch:**

1. Click **Create**
2. Enter name (required), optional code and description
3. Set **Active** status
4. Save

Assign students to batches on the **Students** page. Lecturers then assign exams to batches rather than naming each student individually.

**Deactivating a batch** hides it from student dropdowns but does not delete existing data.

---

### 2.6 Categories

**URL:** `/admin/categories`

Categories organize exams on the public catalog (e.g., Mathematics, Programming, General Knowledge).

**Creating a category:**

1. Click **Create**
2. Enter name and URL slug (auto-generated from name)
3. Optionally choose a **parent category** for nesting
4. Set icon, accent color, and sort order
5. Toggle **Active** — inactive categories are hidden from the public homepage

Categories are shared across the platform. Lecturers pick from active categories when creating exams.

---

### 2.7 Exams (Platform Overview)

**URL:** `/admin/quizzes`

Admins have **read-only oversight** of all exams platform-wide. Exam authoring happens in the Lecturer panel.

**What you can do:**

- Filter by status, visibility, and category
- **View** exam details
- **Publish** a draft exam (if the lecturer left it in draft)
- **View live** — open the public exam page
- **Archive** a published exam
- **Bulk archive** or **delete**

| Status | Meaning |
|--------|---------|
| Draft | Not visible to students |
| Scheduled | Will auto-publish at the configured open time |
| Published | Live and attemptable (subject to assignment rules) |
| Archived | Offline; no new attempts |

---

### 2.8 Exam History Report

**URL:** `/admin/exam-history-report`  
**Permission:** Admin and Super Admin

Review all completed, terminated, and timed-out attempts across the platform.

**Key workflow — publishing held results:**

Some exams are configured to **hold results until published**. After students submit:

1. Open **Exam History Report**
2. Look for the **Results awaiting publication** banner
3. Click **Publish results** for each exam
4. Students receive their scores and any result emails

You cannot hold results from the admin side — lecturers choose this setting when creating the exam.

---

### 2.9 Grade Reports

**URL:** `/admin/grade-reports`  
**Permission:** Admin and Super Admin

Export grade data for any exam.

**Steps:**

1. Select an **exam**
2. Optionally filter by **student batch**
3. Choose **attempt mode**:
   - **Best** — highest score per student
   - **Latest** — most recent attempt per student
   - **All** — every attempt as a separate row
4. Preview the top 10 rows
5. Click **Export Excel** or **Export PDF**

---

### 2.10 Login History Report

**URL:** `/admin/login-history-report`  
**Permission:** Admin and Super Admin

Audit trail of user logins: user name, role, IP address, browser/user agent, and timestamp. Use this for security reviews and troubleshooting access issues.

---

### 2.11 Platform Settings

**URL:** `/admin/settings`  
**Access:** Super Admin only

Central configuration for the entire platform. Settings are organized in tabs:

#### General
- Application name, logo, favicon, certificate logo

#### Theme
- Color presets, primary/accent colors, display and body fonts, base font size

#### Homepage
- Hero section, "How it works" steps, featured exams title, lecturer call-to-action, statistics strip

#### Footer
- Tagline and up to four link columns

#### Registration
- Require email verification (OTP)
- Enable Google OAuth and enter credentials

#### AI
- OpenAI API key, organization ID, and model selection (used for AI question generation)

#### Storage
- Local disk or Amazon S3 (credentials, region, bucket, CDN URL)

#### Email
- SMTP host, port, encryption, credentials, from address/name
- **Send test email** button to verify configuration

#### SEO & Social
- Meta title, description, keywords, Google Analytics ID, Open Graph image, social media URLs

#### Languages
- Show language switcher on admin, lecturer, and public site
- Enable/disable individual locales

#### System
- Cron job instructions and scheduled task list
- **Maintenance mode** toggle and custom message (admin users can still access `/admin` during maintenance)

**Saving secrets:** Password and API key fields show masked hints. Leave a field blank to keep the existing value.

---

### 2.12 Translation Dashboard

**URL:** `/admin/translation-dashboard` (direct URL — not in sidebar)  
**Access:** Super Admin only

Manage multi-language support:

1. Select a locale
2. View translation completeness per language file
3. Edit strings inline and save
4. Add a new locale (copies English as a starting point)
5. Enable or disable locales in the language switcher
6. Delete unused locales

---

### 2.13 Email Templates

**URL:** `/admin/email-templates`

Manage transactional HTML emails sent by the platform (quiz results, OTP verification, announcements, etc.).

**Creating or editing a template:**

1. Set a unique **key**, display **name**, and **subject line**
2. Define **variables** (placeholders like `{{student_name}}`)
3. Write the HTML **body** using those variables
4. **Preview** in a modal
5. **Send test** (Super Admin only) to your own email

---

### 2.14 AI Generation Logs

**URL:** `/admin/ai-generation-logs`

Read-only audit of every AI question generation request:

- Lecturer, prompt, model used
- Token count and cost / free quota usage
- Status and error messages

Filter by status, credit type, today's requests, or failures. The navigation badge shows today's failure count. The table refreshes every 30 seconds.

---

### 2.15 Bulk Notifications

**URL:** `/admin/bulk-notifications`  
**Permission:** Admin and Super Admin

Send announcement emails to platform users.

**Steps:**

1. Choose audience:
   - Active students
   - All students
   - Lecturers and admins
2. Enter subject and body
3. Click **Send announcement**

Emails are queued and sent via your configured SMTP settings.

---

### 2.16 System Health

**URL:** `/admin/system-health`  
**Access:** Super Admin only

Diagnostics dashboard showing:

- Application and PHP version
- Server limits (memory, upload size, execution time)
- Database size
- Writable path checks (storage, bootstrap/cache, `.env`)
- **Clear application cache** button (runs cache, view, and config clear)

---

### 2.17 Typical Admin Workflows

#### Onboard a new class

1. Create a **Student Batch** (`/admin/student-batches`)
2. Create **Student** accounts and assign them to the batch (`/admin/students`)
3. Ensure a **Lecturer** account exists (`/admin/staff`)
4. Lecturer creates and assigns the exam (see Lecturer Guide)
5. Monitor the exam on **Live Monitoring** during the window
6. Export grades from **Grade Reports** afterward

#### Release held exam results

1. Lecturer configures exam with **Hold results until published**
2. Students take and submit the exam
3. Admin opens **Exam History Report**
4. Click **Publish results** for that exam
5. Students can now view scores, reviews, and certificates

#### Configure a new institution deployment

1. Complete the visual installer at `/install` (or manual setup per README)
2. Log in as Super Admin
3. Configure **Settings** → General, Theme, Email, AI
4. Create **Categories** for your subject areas
5. Create **Staff** (admins and lecturers) and **Student Batches**
6. Import or create **Students**
7. Send a **Bulk Notification** welcoming users

---

## 3. Lecturer Guide

**Access:** `/lecturer`  
**Roles:** `lecturer` and `super_admin`

The lecturer panel is where you create exams, manage your question bank, generate AI questions, assign exams to students, and view performance reports.

---

### 3.1 Dashboard

**URL:** `/lecturer`

Your home screen shows:

- **KPI cards** — total exams (published/draft), total attempts, attempts this month
- **Featured exam hero** — your top exam by attempt count with pass rate and average duration
- **Exam performance table** — pass rate and average time for your top 10 exams
- **Exam status donut** — breakdown of published, scheduled, draft, and archived
- **Performance chart** — monthly attempt volume over the last 12 months
- **Side stack** — upcoming scheduled exams and recently created exams

Click **My Exams** or **View all** to open the full exam list.

---

### 3.2 Exams — Overview

**URL:** `/lecturer/quizzes`

List, search, and filter all exams you own.

**List actions:**

| Action | When available | What it does |
|--------|----------------|--------------|
| **Publish** | Draft with ≥1 question | Makes exam live |
| **Archive** | Published | Takes exam offline |
| **Assign to students** | Published | Grant access to batch and/or individuals |
| **Questions** | Any | Open standalone question manager |
| **Edit** | Any | Open the exam wizard |
| **Delete** | Any | Remove exam (soft delete) |

---

### 3.3 Creating an Exam (4-Step Wizard)

**URL:** `/lecturer/quizzes/create`

#### Step 1 — Basic Info

| Field | Description |
|-------|-------------|
| **Title** | Exam name shown to students |
| **URL slug** | Auto-generated from title; used in `/quiz/{slug}` |
| **Category** | Subject area from platform categories |
| **Description** | Rich text overview; use **Generate with AI** for a draft |
| **Cover image** | Upload and crop, or **Generate cover** (procedural image from title) |

#### Step 2 — Settings

| Field | Description |
|-------|-------------|
| **Duration** | Time limit in minutes (countdown timer for students) |
| **Max attempts** | How many times each student can take the exam |
| **Pass percentage** | Minimum score to pass |
| **Visibility** | Public, Private, or Unlisted (see [Visibility](#52-exam-visibility)) |
| **Shuffle questions** | Randomize question order per attempt |
| **Shuffle options** | Randomize answer option order |
| **Results release** | Immediate or hold until admin publishes |
| **Allow review after submit** | Let students review answers on the result page |
| **Negative marking** | Deduct marks for wrong answers |
| **Certificates** | Issue PDF certificate on pass; choose classic or modern style |

#### Step 3 — Questions

Add questions using any combination of:

- **Add question** — manual entry (MCQ single, MCQ multiple, true/false, fill in the blank, short answer)
- **Generate with AI** — streaming AI panel; questions appear in real time
- **Import from Question Bank** — select existing bank questions

The step shows live stats: question count, total marks, and remaining AI credits.

**Question types:**

| Type | Student experience |
|------|-------------------|
| MCQ Single | One correct answer from several options |
| MCQ Multiple | One or more correct answers |
| True / False | Two-option choice |
| Fill in the blank | Text input; may include hints |
| Short answer | Text input (scored server-side) |

#### Step 4 — Review & Publish

Review all settings, then choose a status:

| Status | Behavior |
|--------|----------|
| **Draft** | Save without publishing; only you can see it |
| **Publish now** | Exam goes live immediately |
| **Scheduled** | Auto-publishes when **Exam opens** time arrives |

**Scheduling fields:**

| Field | Description |
|-------|-------------|
| **Exam opens** (`start_at`) | Required for scheduled; optional window start for published exams |
| **Exam closes** (`end_at`) | Blocks new attempts; caps in-progress timers |
| **Force-submit at window close** | Auto-submits all in-progress attempts when the window ends |

**SEO:** Optional meta description and keywords (AI generation available).

After saving, use **Assign to students** from the exam list if the exam is private.

---

### 3.4 Managing Questions (Standalone)

**URL:** `/lecturer/quizzes/{id}/questions`

A dedicated page for question CRUD outside the wizard:

- Add, edit, and delete questions
- **Save to Bank** — copy a question to your question bank
- **Generate with AI** — opens the AI Generator pre-linked to this exam
- Import from question bank

---

### 3.5 Assigning Exams to Students

**Available on:** Published exams only → **Assign to students**

1. Open the assignment modal from the exam list
2. Select one or more **student batches** (all active students in the batch receive access)
3. And/or select individual **students**
4. Confirm

Assignments create enrollments automatically. Students see assigned exams in Discover and **My Exams**.

**Typical class exam setup:**

1. Create exam with **Private** visibility
2. Set **Exam opens** and **Exam closes** for the class period
3. Enable **Force-submit at window close** if needed
4. Publish (or schedule)
5. Assign to the relevant **batch**

---

### 3.6 Question Bank

**URL:** `/lecturer/question-banks`

Reusable questions stored independently of any exam.

**Creating a bank question:**

1. Click **Add Question**
2. Set type, difficulty, collection, and subject (category)
3. Enter question content, options/answers, and marks
4. Save

**Organizing with collections:**

Collections are named groups with a color tag. Create them inline on the question form or via the collection field.

**Bulk operations:**

| Action | Description |
|--------|-------------|
| **Export Excel** | Download all your bank questions |
| **Import Excel** | Bulk upload from spreadsheet |
| **Download Sample Excel** | Get the import template |
| **Duplicate** | Clone a question within the bank |

**Using bank questions in exams:**

- In the exam wizard (Step 3): **Import from Question Bank**
- On Manage Questions page: import selected questions
- API random import: pull N random questions filtered by collection, category, difficulty, or type

---

### 3.7 AI Question Generator

**URL:** `/lecturer/ai-generator`

Dedicated page for bulk AI question creation.

**Workflow:**

1. Choose save destination: **Exam** or **Question Bank**
2. If exam: select target exam (or arrive via `?quiz_id=` link from Manage Questions)
3. Configure generation:
   - Topic / context
   - Number of questions
   - Difficulty, type, marks
   - MCQ option count
   - Language
   - Negative marking
4. Click **Generate** — questions stream in via real-time updates
5. Review cards: expand to see answers and explanations, remove unwanted questions
6. Click **Save & Done**

AI generation uses your institution's OpenAI configuration and consumes AI credits.

---

### 3.8 Reports & Grade Export

**URL:** `/lecturer/reports`

Analytics for a single owned exam. Select the exam from the dropdown (or use `?quiz_id=` in the URL).

**What you see:**

- KPI summary: total attempts, average score, pass rate, average time, top/low score
- Score distribution histogram (0–20%, 21–40%, … 81–100%)
- Recent attempts table (last 8 completed)
- Exam configuration summary

**Export:**

1. Choose attempt mode: **Best**, **Latest**, or **All** per student
2. Click **Export Excel** or **Export PDF**

**Note:** If your exam uses **hold results until published**, you can see attempt data here but only an admin can release scores to students.

---

### 3.9 Profile & Account

**URL:** `/lecturer/profile`

| Section | What you can change |
|---------|---------------------|
| **Profile info** | Name, phone, country, timezone, bio, avatar |
| **Certificate logo** | Logo printed on certificates for your exams (PNG, WebP, or SVG, max 1 MB) |
| **Password** | Current password + new password |

Email address is display-only and cannot be changed in the panel.

---

### 3.10 Typical Lecturer Workflows

#### Public practice quiz

1. **Exams → Create**
2. Set visibility to **Public**
3. Add questions (manual, AI, or bank)
4. **Publish now**
5. Any assigned student (or all students, depending on institution policy) can find and attempt it

#### Timed batch exam with held results

1. Create exam: **Private**, duration 60 min, **Hold results until published**
2. Set **Exam opens** to Monday 9:00 AM, **Exam closes** to Monday 10:30 AM
3. Enable **Force-submit at window close**
4. Add questions, **Publish now**
5. **Assign to students** → select "CS101 Batch A"
6. Ask admin to monitor via **Live Monitoring**
7. After the window, admin **publishes results** from Exam History
8. Export grades from **Reports**

#### Build a reusable question library

1. **Question Bank → Add Question** (or use AI Generator → save to bank)
2. Organize with **collections** and **subjects**
3. Import/export via Excel for bulk work
4. When building exams, import from bank in Step 3

---

## 4. Student Guide

**Access:** Public website — no separate panel login URL beyond `/login`  
**Portal:** `/my/*` after authentication

Students discover exams, take timed tests, view results, and download certificates.

---

### 4.1 Before You Begin

- Your account is **created by an administrator** — you cannot self-register
- You must be **assigned** to an exam (individually or via your batch) before it appears in your catalog
- If email verification is enabled, complete the **OTP verification** before taking exams
- Your administrator assigns you to a **batch** (class group) at account creation

---

### 4.2 Discovering Exams

#### Homepage

**URL:** `/`

The landing page shows featured exams and category highlights. You only see exams assigned to you.

#### Discover Exams

**URL:** `/quizzes`

Browse all your assigned, published exams. Use:

- **Search** — find by title or keyword
- **Category filter** — drill into subject areas
- **Sort** — newest or most popular

Click an exam card to open its detail page.

#### Browse Categories

**URL:** `/categories`

Grid of subject categories. Click a category to filter the exam catalog.

#### Header Search

Use the search bar in the top navigation from any page to find exams quickly.

---

### 4.3 Exam Detail Page

**URL:** `/quiz/{slug}`

Before starting, review:

- Description and cover image
- Number of questions, duration, max attempts, pass percentage
- Whether negative marking applies
- Sample question preview
- Leaderboard (top 10 scores, if attempts exist)
- **Countdown timer** if the exam is scheduled for a future open time

**Start button states:**

| What you see | Meaning |
|--------------|---------|
| **Start exam** | You are assigned and the exam window is open |
| Countdown to open | Exam has not started yet (`start_at` in the future) |
| Window closed | Exam period has ended |
| Not assigned | "This exam has not been assigned to you" |
| Log in to start | You are not signed in |

**Favourites:** Click the heart icon to save the exam for later (`/my/favourites`).

---

### 4.4 Taking an Exam

#### Starting

1. On the exam detail page, click **Start exam**
2. The system checks: you are assigned, the exam is published, the window is open, and you have attempts remaining
3. Any stale in-progress attempt is abandoned automatically
4. You are redirected to the exam panel

#### Exam Panel

**URL:** `/attempt/{attempt}`

Full-screen exam interface (navigation bar is hidden).

**Layout:**

- **Question area** — current question with answer inputs
- **Question palette** — numbered grid showing status of every question
- **Timer** — countdown in the header; at zero the exam auto-submits
- **Navigation** — Previous / Next buttons and palette clicks

**Question palette colors:**

| Color | Status |
|-------|--------|
| Green | Answered |
| Red | Unanswered |
| Yellow / marked | Marked for review |
| Current | Highlighted border |

**Palette filters:** All, Answered, Unanswered, Marked for review

**During the exam:**

- Answers **auto-save** every 30 seconds and when you change a selection
- Click **Mark for review** to flag questions you want to revisit
- Navigate freely between questions until you submit

**Ending the exam:**

1. Click **End test**
2. Confirm in the modal
3. Your attempt is scored and you are redirected to the result page

**If the exam is paused by an administrator:** A full-screen overlay appears. Wait until the admin resumes your session. The page checks every 10 seconds for status changes.

**Proctoring (if enabled):** Switching browser tabs or minimizing the window may be logged as a violation.

---

### 4.5 Viewing Results

**URL:** `/attempt/{attempt}/result`

#### Results available immediately

You see:

- Score ring with percentage
- Pass or Fail badge
- Breakdown: correct, wrong, skipped
- Time taken
- Question-by-question review with your answers, correct answers, and explanations
- **Retry** option if you failed and have attempts remaining
- **Download certificate** if you passed and certificates are enabled

#### Results held

If the institution configured **hold results until published**, you see a pending page after submit:

- "Results Not Yet Published"
- Link to **My Attempts**

Check back after your instructor or administrator releases results.

---

### 4.6 Certificates

#### Download

**URL:** Available from the result page or **My Certificates**

PDF certificate with your name, exam title, score, date, and a verification QR code. Only available after passing and when results are visible.

#### Public Verification

**URL:** `/certificate/{uuid}`

Anyone can visit this link (no login required) to confirm a certificate is authentic. Share this link on LinkedIn or with employers.

---

### 4.7 Student Portal

Access all portal pages from the sidebar or mobile tab bar.

#### Dashboard

**URL:** `/my/dashboard`

Welcome message, summary stats (exams enrolled, pass rate, certificates earned), and your five most recent attempts with quick actions (Continue / View Result).

#### My Exams

**URL:** `/my/quizzes`

All exams you are enrolled in, with enrollment date, question count, latest attempt status, and links to view, continue, or see results.

#### My Attempts

**URL:** `/my/attempts`

Full attempt history with filters:

| Filter | Shows |
|--------|-------|
| All | Every attempt |
| Completed | Finished attempts |
| In Progress | Active or paused attempts |
| Missed | Abandoned or timed out |

#### My Certificates

**URL:** `/my/certificates`

All earned certificates with issue date, score, verify link, and PDF download.

#### My Favourites

**URL:** `/my/favourites`

Exams you saved with the heart icon. Quick links to exam detail and unfavourite toggle.

---

### 4.8 Profile & Settings

**URL:** `/profile`

| Section | What you can do |
|---------|-----------------|
| **Avatar** | Upload a profile photo |
| **Profile info** | Update name, phone, country, timezone |
| **Password** | Change your password |
| **Notifications** | Toggle quiz result emails and weekly digest |
| **Delete account** | Permanently delete your account (requires password confirmation) |

---

### 4.9 Typical Student Workflows

#### Take an assigned exam

1. Log in at `/login`
2. Complete OTP verification if prompted
3. Go to **My Exams** or **Discover**
4. Open the exam → review details → **Start exam**
5. Answer all questions → **End test** → confirm
6. View result (or wait for held results)
7. Download certificate if you passed

#### Retry a failed exam

1. Open the exam detail page or go to **My Attempts**
2. If attempts remain, click **Start exam** or **Retry**
3. Complete the new attempt

#### Save exams for later

1. On any exam detail page, click the **heart** icon
2. Find saved exams under **My Favourites**

---

## 5. Shared Concepts

### 5.1 Exam Visibility

| Setting | Who can see it | Typical use |
|---------|----------------|-------------|
| **Public** | Assigned students in catalog and search | General exams, practice tests |
| **Private** | Only assigned students (not in public browse for unassigned users) | Class exams, internal assessments |
| **Unlisted** | Anyone with the direct URL (if assigned) | Link-only sharing |

Students always need an **assignment** to start an exam, regardless of visibility.

### 5.2 Exam Lifecycle

```
Draft → Scheduled → Published → Archived
         ↓              ↓
    (auto at        (lecturer or
     start_at)       admin action)
```

- **Scheduled** exams auto-publish every minute when `start_at` is reached
- **Force-submit** runs every minute when `force_submit_at_end` is enabled and `end_at` has passed

### 5.3 Assignment vs Enrollment

| Concept | Created by | Purpose |
|---------|-----------|---------|
| **Assignment** | Lecturer (to student or batch) | Grants access to an exam |
| **Enrollment** | System (on assignment or first start) | Tracks student participation |

When an admin moves a student to a different batch, enrollments sync to match the new batch's assignments.

### 5.4 Scoring & Negative Marking

- Each question has a mark value
- **Negative marking** (when enabled) deducts a fraction of marks for wrong answers
- Final statuses: **Completed**, **Timed out** (timer expired), **Terminated** (admin force-submit or window close)

### 5.5 Results Release Modes

| Mode | Student sees results |
|------|---------------------|
| **Immediate** | Right after submit |
| **Hold until published** | After admin clicks Publish results in Exam History |

Certificates and result emails follow the same visibility rules.

### 5.6 Certificates

Enabled per exam by the lecturer. On pass:

1. PDF generated in the background
2. Available on result page and My Certificates
3. Public verification at `/certificate/{uuid}`

Lecturers can set certificate style (classic/modern) and upload a logo in their profile.

---

## 6. Appendix

### 6.1 URL Quick Reference

#### Admin (`/admin`)

| Page | URL |
|------|-----|
| Command Center | `/admin` |
| Live Monitoring | `/admin/live-monitoring` |
| Staff | `/admin/staff` |
| Students | `/admin/students` |
| Student Batches | `/admin/student-batches` |
| Categories | `/admin/categories` |
| Exams | `/admin/quizzes` |
| Exam History | `/admin/exam-history-report` |
| Grade Reports | `/admin/grade-reports` |
| Login History | `/admin/login-history-report` |
| Settings | `/admin/settings` |
| Translation Dashboard | `/admin/translation-dashboard` |
| Email Templates | `/admin/email-templates` |
| AI Generation Logs | `/admin/ai-generation-logs` |
| Bulk Notifications | `/admin/bulk-notifications` |
| System Health | `/admin/system-health` |

#### Lecturer (`/lecturer`)

| Page | URL |
|------|-----|
| Dashboard | `/lecturer` |
| Exams | `/lecturer/quizzes` |
| Create Exam | `/lecturer/quizzes/create` |
| Edit Exam | `/lecturer/quizzes/{id}/edit` |
| Manage Questions | `/lecturer/quizzes/{id}/questions` |
| AI Generator | `/lecturer/ai-generator` |
| Question Bank | `/lecturer/question-banks` |
| Reports | `/lecturer/reports` |
| Profile | `/lecturer/profile` |

#### Student (public site)

| Page | URL |
|------|-----|
| Homepage | `/` |
| Discover Exams | `/quizzes` |
| Categories | `/categories` |
| Exam Detail | `/quiz/{slug}` |
| Login | `/login` |
| Dashboard | `/my/dashboard` |
| My Exams | `/my/quizzes` |
| My Attempts | `/my/attempts` |
| My Certificates | `/my/certificates` |
| My Favourites | `/my/favourites` |
| Profile | `/profile` |
| Exam Panel | `/attempt/{attempt}` |
| Result | `/attempt/{attempt}/result` |
| Certificate Verify | `/certificate/{uuid}` |

### 6.2 Role Permissions Summary

| Feature | Super Admin | Admin | Lecturer | Student |
|---------|:-----------:|:-----:|:--------:|:-------:|
| Platform settings | ✓ | — | — | — |
| System health | ✓ | — | — | — |
| Translation dashboard | ✓ | — | — | — |
| Impersonate users | ✓ | — | — | — |
| Manage staff | ✓ | ✓ | — | — |
| Manage students/batches | ✓ | ✓ | — | — |
| Live monitoring | ✓ | ✓ | — | — |
| Publish held results | ✓ | ✓ | — | — |
| Grade reports (all exams) | ✓ | ✓ | — | — |
| Bulk notifications | ✓ | ✓ | — | — |
| Create/edit exams | ✓ | — | ✓ | — |
| Assign exams | ✓ | — | ✓ | — |
| AI question generation | ✓ | — | ✓ | — |
| Take exams | — | — | — | ✓ |
| View own results | — | — | — | ✓ |

### 6.3 Scheduled Background Tasks

These run automatically every minute (requires cron):

| Task | Effect |
|------|--------|
| Publish scheduled exams | Changes status from Scheduled to Published when `start_at` arrives |
| Close exam windows | Force-submits in-progress attempts when `force_submit_at_end` is on and `end_at` has passed |

Configure cron as described in **Settings → System**.

### 6.4 FAQ

**Why can't I see any exams as a student?**  
You have no exam assignments yet. Ask your lecturer or administrator to assign exams to you or your batch.

**Why can't I register?**  
Accounts are created by administrators. Contact your institution for login credentials.

**I submitted but can't see my score.**  
The exam may use "hold results until published." Wait for your administrator to release results, or check **My Attempts** for status.

**Can I pause my own exam?**  
No. Only administrators can pause, resume, or force-submit attempts via Live Monitoring.

**How do I get AI-generated questions?**  
Lecturers use the AI Generator or inline AI in the exam wizard. Requires OpenAI configuration in platform settings and sufficient AI credits.

**What happens when time runs out?**  
The exam auto-submits. Unanswered questions count as skipped. Status becomes "Timed out."

**Can I change my email address?**  
Not from the student or lecturer profile. Contact your administrator.

**Is the exam the same order for every student?**  
If "Shuffle questions" or "Shuffle options" is enabled, each attempt may differ.

---

*ExamSys User Manual — generated from application version in repository `examsys1-v1`.*
