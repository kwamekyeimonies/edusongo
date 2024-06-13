<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

require_once 'connection.php';
require_once 'sendemail.php';

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

// Handle event addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_event'])) {
    $event_name = $_POST['event_name'];
    $event_date = $_POST['event_date'];

    if (!empty($event_name) && !empty($event_date)) {
        $stmt = $conn->prepare("INSERT INTO event (user_id, event_name, event_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $event_name, $event_date);
        $stmt->execute();
        $stmt->close();
        
        $message= "A new event named <b>$event_name</b> has been added for the date <b>$event_date</b>.";
        
         // Send email notification
         $_SESSION['status']  = sendEventNotification($_SESSION['username'], $message);
    }
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];
    $stmt = $conn->prepare("DELETE FROM event WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $event_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Handle event editing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_event'])) {
    $event_id = $_POST['event_id'];
    $event_name = $_POST['edit_event_name'];
    $event_date = $_POST['edit_event_date'];

    if (!empty($event_name) && !empty($event_date)) {
        $stmt = $conn->prepare("UPDATE event SET event_name = ?, event_date = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $event_name, $event_date, $event_id, $user_id);
        $stmt->execute();
        $stmt->close();
        
         $message= "A new event named <b>$event_name</b> has been updated for the date <b>$event_date</b>.";
        
         // Send email notification
         $_SESSION['status']  = sendEventNotification($_SESSION['username'], $message);
    }
}

// Fetch events
$events = [];
$stmt = $conn->prepare("SELECT id, event_name, event_date FROM event WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Welcome</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#">Event Manager</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>
<div class="container mt-5">
 <?php if (isset($_SESSION['status'])): ?>
        <div class="alert alert-info">
            <?php echo $_SESSION['status']; ?>
        </div>
    <?php endif; ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h3>Your Events</h3>
            <ul class="list-group mb-3">
                <?php foreach ($events as $event): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?php echo htmlspecialchars($event['event_name']) . " - " . htmlspecialchars($event['event_date']); ?>
                        <div>
                            <button class="btn btn-warning btn-sm mr-2" data-toggle="modal" data-target="#editEventModal" data-event-id="<?php echo $event['id']; ?>" data-event-name="<?php echo htmlspecialchars($event['event_name']); ?>" data-event-date="<?php echo htmlspecialchars($event['event_date']); ?>">Edit</button>
                            <form method="post" class="d-inline mb-0">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" name="delete_event" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($events)): ?>
                    <li class="list-group-item">No events found.</li>
                <?php endif; ?>
            </ul>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addEventModal">Add Event</button>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" role="dialog" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">Add Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="event_name">Event Name</label>
                        <input type="text" name="event_name" class="form-control" id="event_name" required>
                    </div>
                    <div class="form-group">
                        <label for="event_date">Event Date</label>
                        <input type="date" name="event_date" class="form-control" id="event_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div class="modal fade" id="editEventModal" tabindex="-1" role="dialog" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="event_id" id="edit_event_id">
                    <div class="form-group">
                        <label for="edit_event_name">Event Name</label>
                        <input type="text" name="edit_event_name" class="form-control" id="edit_event_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_event_date">Event Date</label>
                        <input type="date" name="edit_event_date" class="form-control" id="edit_event_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" name="edit_event" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script>
    $('#editEventModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var eventId = button.data('event-id');
        var eventName = button.data('event-name');
        var eventDate = button.data('event-date');

        var modal = $(this);
        modal.find('#edit_event_id').val(eventId);
        modal.find('#edit_event_name').val(eventName);
        modal.find('#edit_event_date').val(eventDate);
    });
</script>
</body>
</html>

