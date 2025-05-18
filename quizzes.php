<?php

require_once 'header.php';
require_once 'includes/db.php';

// --- Initialize Variables ---
$isUserLoggedIn = isset($_SESSION['user_id']);
$userId = $isUserLoggedIn ? $_SESSION['user_id'] : null;
$activeQuizzes = [];
$userLastScores = [];
$errorMessage = '';
// $searchTerm = trim($_GET['search'] ?? ''); // Removing search for now

// --- Handle Sorting ---
$sortOptions = [
    'title_asc' => 'Title (A-Z)',
    'title_desc' => 'Title (Z-A)',
    'newest' => 'Newest First',
    'oldest' => 'Oldest First'
];
// Default sort order
$currentSort = 'title_asc';
$orderByClause = 'ORDER BY title ASC'; // Default SQL ORDER BY

if (isset($_GET['sort']) && isset($sortOptions[$_GET['sort']])) {
    $currentSort = $_GET['sort'];
    switch ($currentSort) {
        case 'title_desc':
            $orderByClause = 'ORDER BY title DESC';
            break;
        case 'newest':
            $orderByClause = 'ORDER BY quiz_id DESC'; // Assuming higher ID is newer
            // Or use a created_at column if you add one to quizzes: ORDER BY created_at DESC
            break;
        case 'oldest':
             $orderByClause = 'ORDER BY quiz_id ASC'; // Assuming lower ID is older
             // Or use created_at: ORDER BY created_at ASC
            break;
        case 'title_asc':
        default:
            $orderByClause = 'ORDER BY title ASC';
            break;
    }
}

// --- Database Operations ---
try {
    $pdo = connect_db();

    // --- 1. Fetch Active Quizzes (with dynamic sorting) ---
    // Removed search logic
    $sqlQuizzes = "SELECT quiz_id, title, description
                   FROM quizzes
                   WHERE is_active = 1
                   $orderByClause"; // Append the dynamic ORDER BY clause

    error_log("Preparing quiz query: " . $sqlQuizzes);
    $stmtQuizzes = $pdo->prepare($sqlQuizzes); // Prepare the final SQL

    error_log("Executing quiz query...");
    $stmtQuizzes->execute(); // Execute (no parameters needed now)
    error_log("Quiz query execution attempted.");

    $activeQuizzes = $stmtQuizzes->fetchAll(PDO::FETCH_ASSOC);
    $stmtQuizzes->closeCursor();
    error_log("Fetched " . count($activeQuizzes) . " active quizzes using sort: " . $currentSort);


    // --- 2. Fetch Last Attempt Scores (Unchanged - keep the robust version) ---
    if ($isUserLoggedIn && $userId && !empty($activeQuizzes)) {
        // ... (Keep the MAX(attempt_id) score fetching logic from previous correct version) ...
        $quizIds = array_column($activeQuizzes, 'quiz_id');
        $inParams = [];
        $namedPlaceholders = [];
        foreach ($quizIds as $index => $id) {
            $key = ":id_" . $index;
            $namedPlaceholders[] = $key;
            $inParams[$key] = $id;
        }
        if (!empty($namedPlaceholders)) {
             $placeholdersSQL = implode(',', $namedPlaceholders);
             $sqlScores = "SELECT T.quiz_id, T.score, T.total_questions, T.attempt_timestamp
                           FROM quiz_attempts T
                           INNER JOIN (
                               SELECT quiz_id, MAX(attempt_id) as max_attempt_id
                               FROM quiz_attempts
                               WHERE user_id = :userId AND quiz_id IN ($placeholdersSQL)
                               GROUP BY quiz_id
                           ) Latest ON T.quiz_id = Latest.quiz_id AND T.attempt_id = Latest.max_attempt_id
                           WHERE T.user_id = :outerUserId";
             $stmtScores = $pdo->prepare($sqlScores);
             $executeParams = array_merge([':userId' => $userId, ':outerUserId' => $userId], $inParams);
             $stmtScores->execute($executeParams);
             $scoresData = $stmtScores->fetchAll(PDO::FETCH_ASSOC);
             $stmtScores->closeCursor();
             foreach ($scoresData as $scoreData) { $userLastScores[$scoreData['quiz_id']] = $scoreData; }
         }
    } // end if user logged in

} catch (PDOException $e) {
    $errorMessage = "Database error occurred while retrieving quiz data.";
    error_log("Quizzes Page PDO Error (Sort: '{$currentSort}'): " . $e->getMessage());
} catch (Exception $e) {
    $errorMessage = "An error occurred: " . $e->getMessage();
    error_log("Quizzes Page General Error: " . $e->getMessage());
}

// ... (Handle flash messages) ...

?>

<!-- Page Title Header -->
<div class="text-center my-4">
    <h1 class="display-5 fw-bold" style="font-family: 'Playfair Display', serif; color: #2e3a59;">Available Quizzes</h1>
    <p class="lead text-muted">Test your knowledge of Philippine Mythology!</p>
</div>

<!-- *** ADD SORTING CONTROLS *** -->
<div class="container mb-4">
    <div class="row justify-content-end"> <?php // Align to the right ?>
        <div class="col-md-6 col-lg-4">
             <form action="quizzes.php" method="GET" class="d-flex align-items-center">
                 <label for="sortSelect" class="form-label me-2 mb-0 text-nowrap">Sort by:</label>
                 <select class="form-select form-select-sm" id="sortSelect" name="sort" onchange="this.form.submit()">
                     <?php foreach ($sortOptions as $key => $value): ?>
                         <option value="<?php echo $key; ?>" <?php if ($currentSort === $key) echo 'selected'; ?>>
                             <?php echo $value; ?>
                         </option>
                     <?php endforeach; ?>
                 </select>
                 <noscript><button type="submit" class="btn btn-sm btn-secondary ms-2">Sort</button></noscript>
             </form>
        </div>
    </div>
</div>
<!-- ************************** -->


<!-- Display Error Message if needed -->
<?php if (!empty($errorMessage)): ?>
     <div class="container">
         <div class="alert alert-warning alert-dismissible fade show" role="alert">
             <?php echo $errorMessage; ?>
             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
         </div>
     </div>
 <?php endif; ?>

<!-- Quiz Listing -->
<div class="container mb-5">
    <div class="row row-cols-1 row-cols-md-2 g-4">

        <?php if (!empty($activeQuizzes)): ?>
            <?php foreach ($activeQuizzes as $quiz): ?>
                <?php
                // Prepare data (unchanged)
                $quizId = $quiz['quiz_id'];
                $quizTitle = htmlspecialchars($quiz['title']);
                $quizDescription = !empty($quiz['description']) ? nl2br(htmlspecialchars($quiz['description'])) : '<i>No description provided.</i>';
                $takeQuizUrl = "takeQuiz.php?quiz_id=" . $quizId;
                $lastScoreInfo = $userLastScores[$quizId] ?? null;
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm content-section border-0 scroll-fade-in">
                         <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-3"><?php echo $quizTitle; ?></h5>
                            <div class="card-text text-muted flex-grow-1 quiz-description mb-3">
                                <?php echo $quizDescription; ?>
                            </div>
                            <!-- Attempt Info Display (unchanged) -->
                            <div class="attempt-info border-top pt-2 mt-2 mb-3 text-center">
                                <!-- ... (score display logic) ... -->
                                <?php if ($isUserLoggedIn): ?>
                                    <?php if ($lastScoreInfo): ?>
                                        <small class="text-muted">Your Last Score:</small><br>
                                        <strong class="fs-5"><?php echo $lastScoreInfo['score']; ?> / <?php echo $lastScoreInfo['total_questions']; ?></strong>
                                        <small class="d-block text-muted">(<?php echo date('M d, Y H:i', strtotime($lastScoreInfo['attempt_timestamp'])); ?>)</small>
                                    <?php else: ?>
                                        <small class="text-muted"><i>You have not attempted this quiz yet.</i></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <small class="text-muted">Log in to track your attempts.</small>
                                <?php endif; ?>
                            </div>
                            <!-- Action Button Area (unchanged) -->
                            <div class="mt-auto align-self-start w-100 text-center">
                                <!-- ... (conditional button logic) ... -->
                                 <?php if ($isUserLoggedIn): ?>
                                    <a href="<?php echo $takeQuizUrl; ?>" class="btn btn-primary w-100">Take Quiz</a>
                                <?php else: ?>
                                    <a href="login.php?redirect=<?php echo urlencode($takeQuizUrl); ?>" class="btn btn-secondary w-100">Take Quiz</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; // End Foreach Quiz ?>

        <?php elseif (empty($errorMessage)): // If no quizzes found AND no error ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                     <?php /* --- REMOVED Search Term Message --- */ ?>
                     There are currently no active quizzes available. Check back soon!
                </div>
            </div>
        <?php endif; // End If/Elseif for quizzes/error ?>

    </div> <!-- /.row -->
</div> <!-- /.container -->

<?php require_once 'footer.php'; ?>