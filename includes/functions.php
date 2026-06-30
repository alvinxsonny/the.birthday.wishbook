<?php
/**
 * The Birthday Wishbook — Utility Functions
 */

/**
 * Generate a CSRF token and store in session
 */
function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token from request
 */
function validateCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input for display (XSS prevention)
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a unique share token
 */
function generateShareToken(): string {
    return bin2hex(random_bytes(16));
}

/**
 * Get the base URL dynamically
 */
function getBaseUrl(): string {
    return BASE_URL;
}

/**
 * Send a JSON response and exit
 */
function jsonResponse(bool $success, string $message = '', array $data = [], int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

/**
 * Calculate birthday statistics
 */
function calculateBirthdayStats(string $birthdayStr): array {
    $birthday = new DateTime($birthdayStr);
    $now = new DateTime();
    
    // Calculate age
    $age = $now->diff($birthday)->y;
    
    // Calculate next birthday
    $nextBirthday = new DateTime($now->format('Y') . '-' . $birthday->format('m-d'));
    
    // If birthday has passed this year, next one is next year
    if ($nextBirthday < $now) {
        $nextBirthday->modify('+1 year');
    }
    
    // Days until next birthday
    $daysUntil = (int) $now->diff($nextBirthday)->days;
    
    // Is it today?
    $isBirthdayToday = ($now->format('m-d') === $birthday->format('m-d'));
    
    // Zodiac sign
    $zodiac = getZodiacSign((int)$birthday->format('m'), (int)$birthday->format('d'));
    
    // Days alive
    $daysAlive = (int) $birthday->diff($now)->days;
    
    // Next birthday timestamp for JS countdown
    $nextBirthdayTimestamp = $nextBirthday->getTimestamp() * 1000; // milliseconds for JS
    
    return [
        'age'                    => $age,
        'days_until'             => $daysUntil,
        'is_birthday_today'     => $isBirthdayToday,
        'zodiac'                => $zodiac,
        'days_alive'            => $daysAlive,
        'next_birthday'         => $nextBirthday->format('F j'),
        'next_birthday_ts'      => $nextBirthdayTimestamp,
        'birthday_formatted'    => $birthday->format('F j'),
    ];
}

/**
 * Get zodiac sign from month and day
 */
/**
 * Get zodiac sign from month and day
 */
function getZodiacSign(int $month, int $day): array {
    $signs = [
        ['name' => 'Capricorn',   'start' => [1, 1],   'end' => [1, 19]],
        ['name' => 'Aquarius',    'start' => [1, 20],  'end' => [2, 18]],
        ['name' => 'Pisces',      'start' => [2, 19],  'end' => [3, 20]],
        ['name' => 'Aries',       'start' => [3, 21],  'end' => [4, 19]],
        ['name' => 'Taurus',      'start' => [4, 20],  'end' => [5, 20]],
        ['name' => 'Gemini',      'start' => [5, 21],  'end' => [6, 20]],
        ['name' => 'Cancer',      'start' => [6, 21],  'end' => [7, 22]],
        ['name' => 'Leo',         'start' => [7, 23],  'end' => [8, 22]],
        ['name' => 'Virgo',       'start' => [8, 23],  'end' => [9, 22]],
        ['name' => 'Libra',       'start' => [9, 23],  'end' => [10, 22]],
        ['name' => 'Scorpio',     'start' => [10, 23], 'end' => [11, 21]],
        ['name' => 'Sagittarius', 'start' => [11, 22], 'end' => [12, 21]],
        ['name' => 'Capricorn',   'start' => [12, 22], 'end' => [12, 31]],
    ];
    
    foreach ($signs as $sign) {
        $startMonth = $sign['start'][0];
        $startDay   = $sign['start'][1];
        $endMonth   = $sign['end'][0];
        $endDay     = $sign['end'][1];
        
        if (
            ($month === $startMonth && $day >= $startDay) ||
            ($month === $endMonth && $day <= $endDay)
        ) {
            return ['name' => $sign['name'], 'svg' => strtolower($sign['name'])];
        }
    }
    
    return ['name' => 'Unknown', 'svg' => 'star'];
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare('SELECT id, username, email, birthday, share_token, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

/**
 * Redirect helper
 */
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

/**
 * Flash message system using sessions
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Retrieve clean minimal SVG markup for website icons
 */
function getSvgIcon(string $name, string $classes = ''): string {
    $classAttr = 'svg-icon' . ($classes ? ' ' . $classes : '');
    $svgs = [
        'cake' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><rect x="3" y="11" width="18" height="9" rx="2" ry="2"></rect><path d="M12 2v3"></path><path d="M8 8V7a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v1"></path><path d="M3 15h18"></path></svg>',
        'balloon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M12 14a6 6 0 1 0-6-6c0 3.3 2.7 6 6 6Z"></path><path d="M12 14v6"></path><path d="m12 20-2 2h4l-2-2Z"></path></svg>',
        'gift' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><rect x="3" y="8" width="18" height="4" rx="1"></rect><path d="M12 8v13"></path><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"></path><path d="M7.5 8a2.5 2.5 0 0 1 0-5A2.5 2.5 0 0 1 12 8a2.5 2.5 0 0 1 4.5-5a2.5 2.5 0 0 1 0 5"></path></svg>',
        'popper' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M18 3 6 15"></path><path d="m16 8 3-3"></path><path d="m12 12 3-3"></path><path d="m10 5 1 2"></path><path d="M21 9h-2"></path><path d="M18 13v-2"></path><path d="M9 22H3v-6Z"></path></svg>',
        'sparkles' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"></path></svg>',
        'cupcake' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M5 14h14l-1.5 7H6.5L5 14z"></path><path d="M12 3a4 4 0 0 0-4 4 2 2 0 0 0 2 2h4a2 2 0 0 0 2-2 4 4 0 0 0-4-4z"></path><circle cx="12" cy="3" r="1" fill="currentColor"></circle></svg>',
        'confetti' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="5" cy="5" r="1"></circle><circle cx="18" cy="6" r="1.5"></circle><circle cx="12" cy="10" r="1"></circle><circle cx="7" cy="18" r="2"></circle><circle cx="19" cy="17" r="1"></circle><line x1="4" y1="12" x2="6" y2="14"></line><line x1="14" y1="4" x2="16" y2="2"></line><line x1="17" y1="11" x2="19" y2="13"></line></svg>',
        'star' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>',
        'clock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
        'link' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>',
        'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><rect x="3" y="3" width="7" height="9" rx="1.5"></rect><rect x="14" y="3" width="7" height="5" rx="1.5"></rect><rect x="14" y="12" width="7" height="9" rx="1.5"></rect><rect x="3" y="16" width="7" height="5" rx="1.5"></rect></svg>',
        'key' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="7.5" cy="15.5" r="4.5"></circle><path d="M21 3L10.7 13.3M19 5l2 2M16 8l2 2"></path></svg>',
        'warning' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="m10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
        'check' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="12" cy="12" r="10"></circle><path d="m9 12 2 2 4-4"></path></svg>',
        'wave' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M18 11V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v3"></path><path d="M14 10V5a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v5"></path><path d="M10 10.5V6a2 2 0 0 0-2-2v0a2 2 0 0 0-2 2v8"></path><path d="M18 11a2 2 0 0 1 2 2v2a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.8-6.2-2.7L2 16"></path><path d="m14 8 3-3"></path><path d="M20 7.5l2 1.5"></path><path d="M18 13.5l3 1"></path></svg>',
        'search' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
        'clipboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>',
        'edit' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
        'trash' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>',
        'tag' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
        'image' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        'user' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        'email' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'lock' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>',
        'lock-open' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>',
        'new' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>',
        'mailbox' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path></svg>',
        'thought' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>',
        'menu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>',
        'heart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path></svg>',
        'zodiac-aries' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M12 21V9a4 4 0 0 1 4-4h2a2 2 0 0 1 2 2v1c0 2-3 3-6 1m-2-3a4 4 0 0 0-4-4H6a2 2 0 0 0-2 2v1c0 2 3 3 6 1"></path></svg>',
        'zodiac-taurus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="12" cy="14" r="5"></circle><path d="M5 5c2 4 12 4 14 0"></path></svg>',
        'zodiac-gemini' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M9 6v12M15 6v12M6 6h12M6 18h12"></path></svg>',
        'zodiac-cancer' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="7" cy="9" r="3"></circle><circle cx="17" cy="15" r="3"></circle><path d="M10 9h9M14 15H5"></path></svg>',
        'zodiac-leo' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><circle cx="6" cy="14" r="3"></circle><path d="M9 14c3-1 4-9 8-9a3 3 0 0 1 3 3c0 4-4 7-6 10"></path></svg>',
        'zodiac-virgo' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M5 8a3 3 0 0 1 6 0v7M11 8a3 3 0 0 1 6 0v7m0-7a3 3 0 0 1 5 3v5c0 2-2 3-4 1s-1-4-1-4"></path></svg>',
        'zodiac-libra' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M5 19h14M5 14h3a4 4 0 0 1 8 0h3"></path></svg>',
        'zodiac-scorpio' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M5 8a3 3 0 0 1 6 0v7M11 8a3 3 0 0 1 6 0v7m0-4 3 3m0 0-3 1m3-1h-4"></path></svg>',
        'zodiac-sagittarius' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M19 5L5 19M13 5h6v6M8 16l3-3"></path></svg>',
        'zodiac-capricorn' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M6 7a2 2 0 0 1 4 0v10a3 3 0 0 0 6-3v-2a2 2 0 0 1 4 0v1"></path></svg>',
        'zodiac-aquarius' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M4 8l3-3 3 3 3-3 3 3 4-4M4 16l3-3 3 3 3-3 3 3 4-4"></path></svg>',
        'zodiac-pisces' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="' . $classAttr . '"><path d="M5 5c4 4 4 10 0 14M19 5c-4 4-4 10 0 14M4 12h16"></path></svg>',
    ];
    return $svgs[strtolower($name)] ?? '';
}
