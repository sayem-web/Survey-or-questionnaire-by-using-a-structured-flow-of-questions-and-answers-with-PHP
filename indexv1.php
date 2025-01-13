<?php
session_start();

// Sample questions array with relations
$questions = [
    ['question' => 'Please select your loan type?', 'type' => 'radio', 'options' => ['Home loan', 'Business loan'], 'relation' => null],
    ['question' => 'Please select your region?', 'type' => 'radio', 'options' => ['England', 'Scotland'], 'relation' => ['Home loan']],
    ['question' => 'Please select your loan duration?', 'type' => 'text', 'relation' => ['Home loan']],
    ['question' => 'Please select your income type?', 'type' => 'radio', 'options' => ['personal', 'business'], 'relation' => ['Home loan']],
    ['question' => 'How much is your annual salary?', 'type' => 'text', 'relation' => ['Home loan', 'personal']],
    ['question' => 'How much is your annual business turnover?', 'type' => 'text', 'relation' => ['Home loan', 'business']],
    ['question' => 'Not relative questions?', 'type' => 'text', 'relation' => ['Business loan']]
];

$currentQuestionIndex = isset($_SESSION['currentQuestionIndex']) ? $_SESSION['currentQuestionIndex'] : 0;
$selectedOptions = isset($_SESSION['selectedOptions']) ? $_SESSION['selectedOptions'] : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['start_over'])) {
        // Reset the session variables
        session_unset();
        $currentQuestionIndex = 0;
        $selectedOptions = [];
    } else {
        // Save the response
        $response = isset($_POST['response']) ? $_POST['response'] : null;
        $selectedOptions[$currentQuestionIndex] = $response;
        $_SESSION['selectedOptions'] = $selectedOptions;

        // Move to the next question only if a response is provided
        if (isset($_POST['next'])) {
            if (!empty($response)) {
                $currentQuestionIndex++;
                while ($currentQuestionIndex < count($questions) &&
                       isset($questions[$currentQuestionIndex]['relation']) && !empty($questions[$currentQuestionIndex]['relation'])) {
                    $relationMatched = true;
                    foreach ($questions[$currentQuestionIndex]['relation'] as $relation) {
                        if (!in_array($relation, $selectedOptions)) {
                            $relationMatched = false;
                            break;
                        }
                    }
                    if (!$relationMatched) {
                        $currentQuestionIndex++;
                    } else {
                        break;
                    }
                }

                // Check if it's the last question
                if ($currentQuestionIndex >= count($questions)) {
                    echo "<script>alert('You have completed the survey!');</script>";
                    $currentQuestionIndex = count($questions); // Set to count of questions to prevent showing "Not relative questions?"
                }
            } else {
                echo "<script>alert('Please provide a response to proceed.');</script>";
            }
        }

        // Move to the previous question
        if (isset($_POST['back'])) {
            $currentQuestionIndex--;
            while ($currentQuestionIndex >= 0 &&
                   isset($questions[$currentQuestionIndex]['relation']) && !empty($questions[$currentQuestionIndex]['relation'])) {
                $relationMatched = true;
                foreach ($questions[$currentQuestionIndex]['relation'] as $relation) {
                    if (!in_array($relation, $selectedOptions)) {
                        $relationMatched = false;
                        break;
                    }
                }
                if (!$relationMatched) {
                    $currentQuestionIndex--;
                } else {
                    break;
                }
            }
        }
    }

    $_SESSION['currentQuestionIndex'] = $currentQuestionIndex;
}

// Show the question
$question = $currentQuestionIndex < count($questions) ? $questions[$currentQuestionIndex] : null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Survey</title>
</head>
<body>
    <form method="post">
        <div id="questionContainer">
            <?php if ($question): ?>
                <p><?php echo $question['question']; ?></p>
                <?php if ($question['type'] === 'radio' && isset($question['options'])): ?>
                    <?php foreach ($question['options'] as $option): ?>
                        <input type="radio" name="response" value="<?php echo $option; ?>" <?php echo (isset($selectedOptions[$currentQuestionIndex]) && $selectedOptions[$currentQuestionIndex] == $option) ? 'checked' : ''; ?>>
                        <label><?php echo $option; ?></label><br>
                    <?php endforeach; ?>
                <?php elseif ($question['type'] === 'text'): ?>
                    <input type="text" name="response" value="<?php echo isset($selectedOptions[$currentQuestionIndex]) ? $selectedOptions[$currentQuestionIndex] : ''; ?>">
                <?php endif; ?>
            <?php else: ?>
                <p>Survey Completed!</p>
                <button type="submit" name="start_over">Start Over</button>
            <?php endif; ?>
        </div>
        <?php if ($question && $currentQuestionIndex > 0): ?>
            <button type="submit" name="back">Back</button>
        <?php endif; ?>
        <?php if ($question): ?>
            <button type="submit" name="next"><?php echo ($currentQuestionIndex === count($questions) - 1) ? 'Submit' : 'Next'; ?></button>
        <?php endif; ?>
    </form>
</body>
</html>
