<?php
// fine_calculator.php
require_once 'db_config.php';

// Helper to fetch live settings from your system_settings table
function getSystemSetting($key, $default = 0) {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? $result['setting_value'] : $default;
}

function getTieredRepairFee($bookPrice) {
    // Pull the base fee from the database
    $base = getSystemSetting('base_repair_fee', 100.00);

    // Scale the repair tiers based on your Base Fee
    if ($bookPrice < 150) return $base * 0.25;  // ₱25 if base is 100
    if ($bookPrice < 600) return $base * 1.25;  // ₱125 if base is 100
    if ($bookPrice < 2500) return $base * 2.50; // ₱250 if base is 100
    if ($bookPrice < 8000) return $base * 7.25; // ₱725 if base is 100
    
    // For anything very expensive, use the 10% rule[cite: 2]
    if ($bookPrice >= 10000) {
        return round($bookPrice * 0.10, 2);
    }
    
    return $base * 10; // Default max for mid-high range[cite: 2]
}

function getTieredFineRate($bookPrice)
{
    // Pull the 'Anchor' rates from your admin dashboard[cite: 5]
    $rate_low = getSystemSetting('rate_standard', 5.00); 
    $rate_high = getSystemSetting('rate_high', 20.00); 

    // Graduated steps to smooth out the ₱1,500 to ₱10,000 jump[cite: 4, 5]
    if ($bookPrice < 500) {
        return $rate_low; // e.g., ₱5.00
    } 
    elseif ($bookPrice < 1500) {
        return $rate_low * 2; // e.g., ₱10.00
    } 
    elseif ($bookPrice < 3000) {
        return ($rate_low + $rate_high) / 2; // Middle ground: ₱12.50
    } 
    elseif ($bookPrice < 5000) {
        return $rate_high * 0.75; // e.g., ₱15.00
    }
    elseif ($bookPrice < 10000) {
        return $rate_high; // e.g., ₱20.00
    } 
    else {
        // Premium scaling (0.5% of price per day) for ₱10k+[cite: 4]
        return round($bookPrice * 0.005, 2); 
    }
}

function getCalibratedFine($bookPrice, $daysLate, $condition, $severityMultiplier = 1.0) {
    // 1. Get the Daily Rate and the Cap Percentage from DB
    $fineRate = getTieredFineRate($bookPrice);
    $capPercent = getSystemSetting('fine_cap_percent', 0.25); // Defaults to 25%
    
    // 2. Calculate the Raw Overdue Fine
    $rawOverdue = $daysLate * $fineRate;
    
    // 3. Calculate the Ceiling (The maximum the fine is allowed to reach)
    $overdueCap = $bookPrice * $capPercent;
    
    // 4. Use the LOWER of the two values
    // This prevents the fine from ever exceeding the book's value percentage.
    $overduePart = min($rawOverdue, $overdueCap);
    
    $totalAmount = 0;

    if ($condition === 'LOST') {
        $surcharge = $bookPrice * getSystemSetting('replacement_surcharge', 0.10);
        $adminFee = getSystemSetting('admin_fee', 200.00); 
        $totalAmount = $bookPrice + $surcharge + $adminFee + $overduePart;
    } 
    elseif ($condition === 'DAMAGED') {
        $baseRepair = getTieredRepairFee($bookPrice);
        $totalAmount = ($baseRepair * $severityMultiplier) + $overduePart;
    } 
    else {
        $totalAmount = $overduePart;
    }

    return [
        'total' => round($totalAmount, 2),
        'fine_rate' => $fineRate,
        'overdue_part' => $overduePart,
        'is_capped' => ($rawOverdue > $overdueCap) // Tells the UI if the cap was hit
    ];
}
