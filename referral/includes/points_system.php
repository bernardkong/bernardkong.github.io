<?php

/**
 * Awards points to a GP for a referral
 * 
 * @param PDO $db Database connection
 * @param int $gpId The GP's ID
 * @param int $referralId The referral ID
 * @return bool Success status
 */
function awardReferralPoints($db, $gpId, $referralId) {
    try {
        // Check if GP exists in honour_points table
        $stmt = $db->prepare("SELECT * FROM honour_points WHERE gp_id = ?");
        $stmt->execute([$gpId]);
        $pointsRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no record exists, create one
        if (!$pointsRecord) {
            $stmt = $db->prepare("
                INSERT INTO honour_points (gp_id, points, level, total_referrals, successful_referrals) 
                VALUES (?, 0, 'Bronze', 0, 0)
            ");
            $stmt->execute([$gpId]);
        }

        // Update points and total referrals
        $stmt = $db->prepare("
            UPDATE honour_points 
            SET 
                points = points + 1,
                total_referrals = total_referrals + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE gp_id = ?
        ");
        $stmt->execute([$gpId]);

        // Log points history
        $stmt = $db->prepare("
            INSERT INTO points_history (gp_id, referral_id, points_awarded, action_type) 
            VALUES (?, ?, 1, 'new_referral')
        ");
        $stmt->execute([$gpId, $referralId]);

        // Update GP level based on total points
        updateGPLevel($db, $gpId);

        return true;

    } catch (Exception $e) {
        error_log("Error in awardReferralPoints: " . $e->getMessage());
        return false;
    }
}

/**
 * Updates a GP's level based on their total points
 * 
 * @param PDO $db Database connection
 * @param int $gpId The GP's ID
 */
function updateGPLevel($db, $gpId) {
    try {
        $stmt = $db->prepare("SELECT points FROM honour_points WHERE gp_id = ?");
        $stmt->execute([$gpId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $points = $result['points'];
            $newLevel = 'Bronze'; // Default level

            // Define level thresholds
            if ($points >= 1000) {
                $newLevel = 'Diamond';
            } elseif ($points >= 500) {
                $newLevel = 'Platinum';
            } elseif ($points >= 250) {
                $newLevel = 'Gold';
            } elseif ($points >= 100) {
                $newLevel = 'Silver';
            }

            // Update level if needed
            $stmt = $db->prepare("
                UPDATE honour_points 
                SET level = ?, updated_at = CURRENT_TIMESTAMP
                WHERE gp_id = ? AND level != ?
            ");
            $stmt->execute([$newLevel, $gpId, $newLevel]);
        }

    } catch (Exception $e) {
        error_log("Error in updateGPLevel: " . $e->getMessage());
    }
}

/**
 * Awards points to a GP for a successful referral
 * 
 * @param PDO $db Database connection
 * @param int $gpId The GP's ID
 * @param int $referralId The referral ID
 * @return bool Success status
 */
function awardSuccessfulReferralPoints($db, $gpId, $referralId) {
    try {
        // Update points and successful referrals count
        $stmt = $db->prepare("
            UPDATE honour_points 
            SET 
                points = points + 2,
                successful_referrals = successful_referrals + 1,
                updated_at = CURRENT_TIMESTAMP
            WHERE gp_id = ?
        ");
        $stmt->execute([$gpId]);

        // Log points history
        $stmt = $db->prepare("
            INSERT INTO points_history (gp_id, referral_id, points_awarded, action_type) 
            VALUES (?, ?, 2, 'successful_referral')
        ");
        $stmt->execute([$gpId, $referralId]);

        // Update GP level based on total points
        updateGPLevel($db, $gpId);

        return true;

    } catch (Exception $e) {
        error_log("Error in awardSuccessfulReferralPoints: " . $e->getMessage());
        return false;
    }
}