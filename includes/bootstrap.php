<?php
/**
 * bootstrap.php
 * Loaded at the top of every page: starts the session, loads every class,
 * and provides small helper functions used throughout the app.
 */

session_start();

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Category.php';
require_once __DIR__ . '/../classes/Ingredient.php';
require_once __DIR__ . '/../classes/RecipeStep.php';
require_once __DIR__ . '/../classes/Recipe.php';
require_once __DIR__ . '/../classes/Bookmark.php';
require_once __DIR__ . '/../classes/ActivityLog.php';

/** Escapes a string for safe HTML output. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Returns the logged-in User, or null if nobody is logged in. */
function currentUser(): ?User
{
    static $cached = false; // false = "not looked up yet" (null is a valid result)
    if ($cached === false) {
        $cached = isset($_SESSION['user_id']) ? User::findById((int) $_SESSION['user_id']) : null;
    }
    return $cached;
}

/** Sends the visitor to login.php if they aren't authenticated. */
function requireLogin(): User
{
    $user = currentUser();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

/** Like requireLogin(), but also requires the Admin role. */
function requireAdmin(): User
{
    $user = requireLogin();
    if (!$user->isAdmin()) {
        http_response_code(403);
        require __DIR__ . '/header.php';
        echo '<div class="empty-state"><div class="ic">🔒</div>Only Admins can access this page.</div>';
        require __DIR__ . '/footer.php';
        exit;
    }
    return $user;
}

/** Days remaining until the next Christmas (Dec 25), used for the holiday banner. */
function daysUntilChristmas(): int
{
    $now = new DateTime('today');
    $year = (int) $now->format('Y');
    $target = new DateTime("$year-12-25");
    if ($target < $now) {
        $target = new DateTime(($year + 1) . '-12-25');
    }
    return (int) $now->diff($target)->days;
}

const AVATAR_COLORS = ['#C1613B', '#7C8B5D', '#DDA23B', '#8C5E4E', '#5F6D45', '#B4793F'];
const CATEGORY_ICONS = [
    'Mains' => '🍛', 'Desserts' => '🍰', 'Appetizers' => '🥟', 'Soups' => '🍲',
    'Breads' => '🍞', 'Holiday Specials' => '🎄', 'Beverages' => '🥤',
];
const GENDER_OPTIONS = ['Female', 'Male', 'Non-binary', 'Prefer not to say'];

function avatarColor(int $userId): string
{
    return AVATAR_COLORS[$userId % count(AVATAR_COLORS)];
}

function categoryIcon(?string $categoryName): string
{
    return CATEGORY_ICONS[$categoryName] ?? '🍽️';
}

function formatDate(?string $datetime): string
{
    if (!$datetime) return '—';
    return (new DateTime($datetime))->format('M j, Y');
}

function formatDateTime(?string $datetime): string
{
    if (!$datetime) return '—';
    return (new DateTime($datetime))->format('M j, g:i A');
}
