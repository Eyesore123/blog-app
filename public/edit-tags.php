<?php

$validToken = getenv('ADMIN_SETUP_TOKEN');
$providedToken = $_GET['token'] ?? '';

if (!$validToken || $providedToken !== $validToken) {
    echo "Unauthorized";
    exit(1);
}

// Get the DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');

if (!$databaseUrl) {
    echo "<p>DATABASE_URL is not set!</p>";
    exit;
}

// Parse the DATABASE_URL
$dbParts = parse_url($databaseUrl);
$host = $dbParts['host'] ?? '';
$port = $dbParts['port'] ?? 5432;
$database = ltrim($dbParts['path'] ?? '', '/');
$username = $dbParts['user'] ?? '';
$password = $dbParts['pass'] ?? '';

// Connect to the database
$conn = pg_connect("host=$host port=$port dbname=$database user=$username password=$password");

// Check if the connection was successful
if (!$conn) {
    echo "Connection failed: " . pg_last_error();
    exit;
}

// Function to get all tags
function getTags() {
    global $conn;
    $query = "SELECT * FROM tags";
    $result = pg_query($conn, $query);
    $tags = array();
    while ($row = pg_fetch_assoc($result)) {
        $tags[] = $row;
    }
    return $tags;
}

// Function to edit a tag
function editTag($id, $name) {
    global $conn;
    $query = "UPDATE tags SET name = '$name' WHERE id = $id";
    pg_query($conn, $query);
}

// Function to delete a tag
function deleteTag($id) {
    global $conn;
    $query = "DELETE FROM tags WHERE id = $id";
    pg_query($conn, $query);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["edit"])) {
        $id = $_POST["id"];
        $name = $_POST["name"];
        editTag($id, $name);
    } elseif (isset($_POST["delete"])) {
        $id = $_POST["id"];
        deleteTag($id);
    }
}

// Display the tags
$tags = getTags();
?>

<form action="" method="post">
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
        <?php foreach ($tags as $tag) { ?>
        <tr>
            <td><?php echo $tag["id"]; ?></td>
            <td><?php echo $tag["name"]; ?></td>
            <td>
                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo $tag["id"]; ?>">
                    <input type="text" name="name" value="<?php echo $tag["name"]; ?>">
                    <input type="submit" name="edit" value="Edit">
                </form>
            </td>
            <td>
                <form action="" method="post">
                    <input type="hidden" name="id" value="<?php echo $tag["id"]; ?>">
                    <input type="submit" name="delete" value="Delete">
                </form>
            </td>
        </tr>
        <?php } ?>
    </table>
</form>