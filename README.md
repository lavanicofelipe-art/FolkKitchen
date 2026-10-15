# FolksKitchen — Family Recipe Digital Archive (PHP + MySQL)

A server-side PHP web app built with an object-oriented (class-based) data
model that mirrors the FolksKitchen database schema/ERD: Users, Categories,
Recipes, Ingredients, Recipe_Steps, Bookmarks, and Activity_Logs.

Includes a full **Log In** and **Register Account** system — anyone can
create their own account (First Name, Last Name, Username, Email,
Birthdate, Gender, City, Password, and Role), no admin required.

## Requirements

- PHP 8.0+ with the **PDO MySQL** extension (`pdo_mysql`) enabled
- MySQL or MariaDB
- Any local server stack works: XAMPP, WAMP, MAMP, Laragon, or plain
  `php -S` + a MySQL install

## Setup

### 1. Create the database

**Setting up fresh?** Run these three files in `database/`, in order:

```
mysql -u root -p < database/00_create_database.sql
mysql -u root -p folkskitchen < database/folkskitchen_schema.sql
mysql -u root -p folkskitchen < database/seed.sql
```

If you're using phpMyAdmin instead: create a database named `folkskitchen`
yourself (Databases tab → enter the name → Create), select it, then use
the **Import** tab to run `folkskitchen_schema.sql` followed by `seed.sql`.

**Already have an older FolksKitchen database** (from before registration
existed)? Don't re-import the schema — instead run the migration script,
which adds the new columns in place without touching your existing data:

```
mysql -u root -p < database/01_migrate_add_registration_fields.sql
```

It adds `Username`, `Birthdate`, `Gender`, and `City` to `Users`, backfills
a placeholder username for any existing accounts, and renames the old
`Chef`/`Relative` roles to `Admin`/`Participant`.

### 2. Point the app at your database

Edit `config/database.php`:

```php
return [
    'host'   => 'localhost',
    'dbname' => 'folkskitchen',
    'user'   => 'root',
    'pass'   => '',        // your MySQL password, if any
    'charset'=> 'utf8mb4',
];
```

The defaults match a stock XAMPP/WAMP install (root, no password).

### 3. Run it

**Option A — XAMPP/WAMP/MAMP:** copy the whole `folkskitchen-project`
folder into `htdocs` (XAMPP) or `www` (WAMP), start Apache + MySQL from
the control panel, then visit `http://localhost/folkskitchen-project/`.

**Option B — PHP's built-in server** (no Apache needed, MySQL still
required): from inside the project folder, run

```
php -S localhost:8000
```

then open `http://localhost:8000/login.php`.

### 4. Log in or register

**Log in** with any seeded family member — every one shares the password
**`family123`**:

| Email                             | Role        |
|-------------------------------------|-------------|
| odysseus@folkskitchen.family         | Admin       |
| maria@folkskitchen.family            | Admin       |
| elena@folkskitchen.family            | Participant |
| marcus@folkskitchen.family           | Participant |
| sophia@folkskitchen.family           | Participant |

**Or register** a brand new account from the login page ("Create an
account →" / `register.php`). Registration asks for: First Name, Last
Name, Username, Email, Birthdate, Gender, City, Password, Re-enter
Password, and Role (Admin or Participant). On success you're logged in
immediately.

Admins can create, edit, and delete recipes and manage family members.
Participants can browse, search, and bookmark.

## Project structure

```
folkskitchen-project/
├── config/
│   └── database.php          ← your DB connection settings
├── classes/                  ← the OOP data model (one class per table)
│   ├── Database.php          ← PDO connection singleton
│   ├── User.php               ← auth, registration, profile
│   ├── Category.php
│   ├── Recipe.php
│   ├── Ingredient.php
│   ├── RecipeStep.php
│   ├── Bookmark.php
│   └── ActivityLog.php
├── includes/
│   ├── bootstrap.php         ← session start, class loading, auth helpers
│   ├── header.php / footer.php  ← shared page shell + sidebar nav
│   └── recipe_card.php       ← reusable recipe card partial
├── assets/
│   └── style.css             ← all styling (warm kraft-paper theme)
├── database/
│   ├── 00_create_database.sql
│   ├── folkskitchen_schema.sql        ← full schema, fresh installs
│   ├── 01_migrate_add_registration_fields.sql  ← upgrade an old install
│   └── seed.sql                       ← demo data
├── login.php / logout.php
├── register.php                ← create a new account
├── index.php                    ← dashboard
├── recipes.php                  ← search + browse
├── recipe_detail.php
├── recipe_form.php              ← add/edit (Admin only)
├── recipe_delete.php
├── bookmark_toggle.php
├── bookmarks.php
├── members.php                  ← family member management (Admin only)
├── activity.php                 ← activity log
└── profile.php
```

## How the OOP layer works

Every table has a matching PHP class in `classes/`. Each class:

- Has typed public properties matching the table's columns (e.g.
  `Recipe::$title`, `User::$birthdate`)
- Hydrates itself from a database row in its constructor
- Exposes static finder methods (`Recipe::findById()`, `Recipe::search()`,
  `User::findByEmail()`, `User::findByUsername()`, …) that run a prepared
  PDO query and return instances of the class
- Exposes instance methods for behavior (`$recipe->totalTime()`,
  `$user->isAdmin()`, `$user->age()`, `$recipe->save()`, `$recipe->delete()`)

`Database.php` is a singleton — the first class that needs a connection
opens it via `Database::getConnection()`, and every other class reuses
that same `PDO` instance for the rest of the request.

The page scripts (`index.php`, `register.php`, etc.) contain almost no SQL
themselves — they just call methods on these classes and render the
result as HTML. All database access goes through **prepared statements**,
so user input (search terms, registration fields) is never concatenated
directly into a query.

## How registration and login work

- **`register.php`** collects First Name, Last Name, Username, Email,
  Birthdate, Gender, City, Password, and Re-entered Password, then a Role
  dropdown (Admin or Participant). It validates on the server: required
  fields, a valid email, a valid birthdate, matching passwords (6+
  characters), and that both the email and username are unique
  (`User::findByEmail()` / `User::findByUsername()`). On success it
  hashes the password with `password_hash()`, saves the new `User`, logs
  the person in immediately (`$_SESSION['user_id']`), and redirects to
  the dashboard.
- **`login.php`** looks the account up by email and checks the password
  with `password_verify()` against `Password_Hash`.
- **`members.php`** gives Admins a second way to add accounts directly
  (useful for onboarding a family member who won't register themselves),
  using the same validation and the same `User` class.
- Session state (`$_SESSION['user_id']`) is the single source of truth
  for "who's logged in" — `currentUser()`, `requireLogin()`, and
  `requireAdmin()` in `includes/bootstrap.php` all read from it.

## Notes on a couple of schema-driven design choices

- **`Activity_Logs.Recipe_ID` is a required (`NOT NULL`) foreign key**
  with no `ON DELETE` rule in the schema. That means a log entry can only
  ever point at a recipe that still exists. So: recipe create/update and
  bookmark actions are logged (they reference a real, current recipe),
  but a recipe *deletion* and "member added" events are **not** written
  to `Activity_Logs` — there's no recipe left (or no recipe at all) for
  the row to legally reference. Deleting a recipe also removes its own
  prior log rows, since MySQL would otherwise block the delete with a
  foreign-key error.
- Deleting a recipe cascades in PHP (not in the database) to its
  ingredients, steps, and bookmarks, since the schema doesn't define
  `ON DELETE CASCADE` on those foreign keys.
- **Login is by email, not username.** Username is collected at
  registration and shown on the Family Members and Profile pages, but
  sign-in still uses email + password. (Easy to change to
  "email-or-username" in `login.php` by trying `User::findByUsername()`
  as a fallback if you'd rather support both.)

## Customizing

- **Seed data** — edit `database/seed.sql` and re-import.
- **Gender options** — edit the `GENDER_OPTIONS` constant in
  `includes/bootstrap.php`.
- **Colors/fonts** — edit the CSS variables at the top of
  `assets/style.css` and the Google Fonts `<link>` in `includes/header.php`.
- **Navigation** — edit the `$navItems` array in `includes/header.php`,
  then add the corresponding `.php` page.
- **Roles** — role checks are just `$user->isAdmin()`; search for it to
  find every gated feature.

## Credits / context

Built for the "PRELIM: Create a Persona and Design an ERD" project —
persona: Odysseus Odyssey, family restaurant chef and culinary custodian;
problem: fragile, scattered family recipes at risk of being lost between
generations.
