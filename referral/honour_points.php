<?php
// Start session
session_start();

// Get database connection
$db = require_once 'config/db.php';
if (!$db) {
    die("Database connection failed");
}

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$gps = [];
$points_history = [];
$programs = [];
$error_message = null;
$history_error = null;
$program_error = null;

// Fetch GPs with their honour points
try {
    $stmt = $db->query("SELECT 
        gd.id,
        gd.gp_name as first_name,
        '' as last_name,
        COALESCE(hp.points, 0) as points,
        CASE 
            WHEN COALESCE(hp.points, 0) >= 20 THEN 'Gold'
            WHEN COALESCE(hp.points, 0) >= 10 THEN 'Silver'
            ELSE 'Bronze'
        END as level,
        COALESCE(hp.total_referrals, 0) as total_referrals,
        COALESCE(hp.successful_referrals, 0) as successful_referrals
    FROM gp_doctors gd
    LEFT JOIN honour_points hp ON gd.id = hp.gp_id
    WHERE gd.is_active = 1
    ORDER BY hp.points DESC");
    
    if ($stmt === false) {
        throw new PDOException("Query failed");
    }
    
    $gps = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "An error occurred while fetching GP data.";
}

// Fetch recent points history
try {
    $stmt = $db->query("SELECT 
        ph.*,
        gd.gp_name as gp_name
    FROM points_history ph
    JOIN gp_doctors gd ON ph.gp_id = gd.id
    ORDER BY ph.created_at DESC
    LIMIT 10");
    
    if ($stmt === false) {
        throw new PDOException("Query failed");
    }
    
    $points_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $history_error = "An error occurred while fetching points history.";
}

// Fetch GP Programs
try {
    $stmt = $db->query("SELECT 
        program_id,
        program_title,
        program_description,
        program_link,
        program_image,
        is_published
    FROM gp_programs
    ORDER BY created_at DESC");
    
    if ($stmt === false) {
        throw new PDOException("Query failed");
    }
    
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $program_error = "An error occurred while fetching program data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Honour Points System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--primary-color);
        }
        
        .container {
            max-width: 1200px;
            padding: 2rem;
        }
        
        .page-title {
            color: var(--primary-color);
            margin-bottom: 2rem;
            font-weight: 600;
            position: relative;
            padding-bottom: 10px;
        }
        
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--accent-color);
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            padding: 1.25rem;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-header h5 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table {
            margin: 0;
        }
        
        .table th {
            border-top: none;
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .bg-bronze {
            background-color: #cd7f32;
            color: white;
        }
        
        .bg-warning {
            background-color: #f1c40f !important;
            color: #000 !important;
        }
        
        .bg-secondary {
            background-color: #95a5a6 !important;
        }
        
        .rules-list li {
            padding: 8px 0;
            position: relative;
            padding-left: 25px;
        }
        
        .rules-list li:before {
            content: '•';
            color: var(--accent-color);
            position: absolute;
            left: 0;
            font-size: 1.5em;
            line-height: 1;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <h2 class="page-title">GP Honour Points System</h2>
        
        <!-- Points System Rules -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-info-circle-fill text-primary"></i> Points System Rules</h5>
            </div>
            <div class="card-body">
                <ul class="rules-list">
                    <li>GP receives 1 point for each referral made</li>
                    <li>Additional 2 points when patient successfully attends the hospital</li>
                    <li>Levels: Bronze (0-9 points), Silver (10-19 points), Gold (20+ points)</li>
                </ul>
            </div>
        </div>

        <!-- GP Rankings Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-trophy-fill text-warning"></i> GP Rankings</h5>
            </div>
            <div class="card-body table-responsive">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash"></i> Rank</th>
                                <th><i class="bi bi-person"></i> GP Name</th>
                                <th><i class="bi bi-star-fill"></i> Total Points</th>
                                <th><i class="bi bi-award"></i> Level</th>
                                <th><i class="bi bi-diagram-2"></i> Total Referrals</th>
                                <th><i class="bi bi-check-circle"></i> Successful Referrals</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $rank = 1;
                            foreach ($gps as $gp) {
                                $levelClass = 'bg-secondary';
                                switch($gp['level']) {
                                    case 'Gold':
                                        $levelClass = 'bg-warning';
                                        break;
                                    case 'Silver':
                                        $levelClass = 'bg-secondary';
                                        break;
                                    case 'Bronze':
                                        $levelClass = 'bg-bronze';
                                        break;
                                }
                                ?>
                                <tr>
                                    <td><?php echo $rank++; ?></td>
                                    <td><?php echo htmlspecialchars($gp['first_name'] . ' ' . $gp['last_name']); ?></td>
                                    <td><?php echo $gp['points']; ?></td>
                                    <td><span class="badge <?php echo $levelClass; ?>"><?php echo $gp['level']; ?></span></td>
                                    <td><?php echo $gp['total_referrals']; ?></td>
                                    <td><?php echo $gp['successful_referrals']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Points History -->
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-clock-history text-primary"></i> Recent Points Activity</h5>
            </div>
            <div class="card-body table-responsive">
                <?php if ($history_error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($history_error); ?></div>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="bi bi-calendar3"></i> Date</th>
                                <th><i class="bi bi-person"></i> GP Name</th>
                                <th><i class="bi bi-activity"></i> Action</th>
                                <th><i class="bi bi-plus-circle"></i> Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($points_history as $history) { ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i', strtotime($history['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($history['gp_name']); ?></td>
                                    <td><?php echo htmlspecialchars($history['action_type']); ?></td>
                                    <td><?php echo $history['points']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- GP Affiliated Programs -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-briefcase-fill text-primary"></i> GP Affiliated Programs</h5>
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProgramModal">
                        <i class="bi bi-plus-circle"></i> Add Program
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($program_error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($program_error); ?></div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($programs as $program): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100">
                                    <img src="<?php echo htmlspecialchars($program['program_image']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($program['program_title']); ?>"
                                         style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($program['program_title']); ?></h5>
                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($program['program_description'])); ?></p>
                                        <a href="<?php echo htmlspecialchars($program['program_link']); ?>" 
                                           class="btn btn-primary" 
                                           target="_blank">Learn More</a>
                                        
                                        <?php if (isset($_SESSION['admin_id'])): ?>
                                            <div class="mt-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input program-status" 
                                                           type="checkbox" 
                                                           id="program<?php echo $program['program_id']; ?>"
                                                           data-program-id="<?php echo $program['program_id']; ?>"
                                                           <?php echo $program['is_published'] ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="program<?php echo $program['program_id']; ?>">
                                                        Published
                                                    </label>
                                                </div>
                                                <button class="btn btn-danger btn-sm mt-2 delete-program" 
                                                        data-program-id="<?php echo $program['program_id']; ?>">
                                                    <i class="bi bi-trash"></i> Remove
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Add Program Modal -->
        <div class="modal fade" id="addProgramModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Program</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="addProgramForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Program Title</label>
                                <input type="text" class="form-control" name="program_title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="program_description" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Program Link</label>
                                <input type="url" class="form-control" name="program_link" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Program Image</label>
                                <input type="file" class="form-control" name="program_image" accept="image/*" required>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_published" id="publishStatus">
                                <label class="form-check-label" for="publishStatus">Publish immediately</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Program</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            // Program status toggle
            document.querySelectorAll('.program-status').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const programId = this.dataset.programId;
                    const isPublished = this.checked;
                    
                    fetch('api/update_program_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            program_id: programId,
                            is_published: isPublished
                        })
                    });
                });
            });

            // Delete program
            document.querySelectorAll('.delete-program').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this program?')) {
                        const programId = this.dataset.programId;
                        
                        fetch('api/delete_program.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                program_id: programId
                            })
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });

            // Add program form submission
            document.getElementById('addProgramForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                
                fetch('api/add_program.php', {
                    method: 'POST',
                    body: formData
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          location.reload();
                      } else {
                          alert('Error adding program: ' + data.message);
                      }
                  });
            });
        </script>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>