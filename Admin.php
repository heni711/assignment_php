<?php
$host = 'localhost';
$db = 'ecommerce'; 
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect: " . $e->getMessage());
}

// Initialize message variable
$message = '';

// Function to validate inputs
function validateInput($name, $description, $quantity, $price, $size, $color) {
    $errors = [];
    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Shoe name must be at least 2 characters long.";
    }
    if (empty($description) || strlen($description) < 10) {
        $errors[] = "Shoe description must be at least 10 characters long.";
    }
    if (!is_numeric($quantity) || $quantity < 0) {
        $errors[] = "Quantity must be a non-negative integer.";
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = "Price must be a non-negative number.";
    }
    if (empty($size) || !preg_match("/^\d+(\.\d+)?$/", $size)) {
        $errors[] = "Size must be a valid number.";
    }
    if (empty($color)) {
        $errors[] = "Color cannot be empty.";
    }
    return $errors;
}

// Handle POST requests for Create, Update, and Delete
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shoe_id = isset($_POST['shoe_id']) ? (int)$_POST['shoe_id'] : null;
    $name = htmlspecialchars(trim($_POST['shoe_name']));
    $description = htmlspecialchars(trim($_POST['shoe_description']));
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $size = htmlspecialchars(trim($_POST['size']));
    $color = htmlspecialchars(trim($_POST['color']));

    $validationErrors = validateInput($name, $description, $quantity, $price, $size, $color);

    if (empty($validationErrors)) {
        if (isset($_POST['add'])) {
            // Add shoe
            $stmt = $pdo->prepare("INSERT INTO shoes (ShoeName, ShoeDescription, QuantityAvailable, Price, Size, Color) VALUES (?, ?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$name, $description, $quantity, $price, $size, $color]);
                $message = "Shoe added successfully!";
            } catch (PDOException $e) {
                $message = "Error adding shoe: " . $e->getMessage();
            }
        } elseif (isset($_POST['edit'])) {
            // Edit shoe
            $stmt = $pdo->prepare("UPDATE shoes SET ShoeName = ?, ShoeDescription = ?, QuantityAvailable = ?, Price = ?, Size = ?, Color = ? WHERE ShoeID = ?");
            try {
                $stmt->execute([$name, $description, $quantity, $price, $size, $color, $shoe_id]);
                $message = "Shoe updated successfully!";
            } catch (PDOException $e) {
                $message = "Error updating shoe: " . $e->getMessage();
            }
        } elseif (isset($_POST['delete'])) {
            // Delete shoe
            $stmt = $pdo->prepare("DELETE FROM shoes WHERE ShoeID = ?");
            if ($stmt->execute([$shoe_id])) {
                $message = "Shoe deleted successfully.";
            } else {
                $message = "Error deleting the shoe.";
            }
        }
    } else {
        $message = implode('<br>', $validationErrors);
    }
}

// Fetch all shoes for display
$stmt = $pdo->query("SELECT * FROM shoes");
$shoes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Manage Shoes</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Manage Shoes</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-4">
            <h3>Add New Shoe</h3>
            <div class="form-group">
                <label for="shoe_name">Shoe Name</label>
                <input type="text" name="shoe_name" class="form-control" >
            </div>
            <div class="form-group">
                <label for="shoe_description">Shoe Description</label>
                <textarea name="shoe_description" class="form-control" ></textarea>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity Available</label>
                <input type="number" name="quantity" class="form-control"  min="0">
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" class="form-control"  min="0">
            </div>
            <div class="form-group">
                <label for="size">Size</label>
                <input type="text" name="size" class="form-control" >
            </div>
            <div class="form-group">
                <label for="color">Color</label>
                <input type="text" name="color" class="form-control" >
            </div>
            <button type="submit" name="add" class="btn btn-primary">Add Shoe</button>
        </form>

        <h3>Current Shoes</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Size</th>
                    <th>Color</th> 
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($shoes): ?>
                    <?php foreach ($shoes as $shoe): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($shoe['ShoeID']); ?></td>
                        <td><?php echo htmlspecialchars($shoe['ShoeName']); ?></td>
                        <td><?php echo htmlspecialchars($shoe['ShoeDescription']); ?></td>
                        <td><?php echo htmlspecialchars($shoe['QuantityAvailable']); ?></td>
                        <td><?php echo htmlspecialchars($shoe['Price']); ?></td>
                        <td><?php echo htmlspecialchars($shoe['Size']); ?></td>
                        <td><?php echo htmlspecialchars($shoe['Color']); ?></td>
                        <td>
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#editModal<?php echo $shoe['ShoeID']; ?>">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this shoe?');">
                                <input type="hidden" name="shoe_id" value="<?php echo htmlspecialchars($shoe['ShoeID']); ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $shoe['ShoeID']; ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edit Shoe</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="shoe_id" value="<?php echo htmlspecialchars($shoe['ShoeID']); ?>">
                                        <div class="form-group">
                                            <label for="shoe_name">Shoe Name</label>
                                            <input type="text" name="shoe_name" class="form-control" value="<?php echo htmlspecialchars($shoe['ShoeName']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="shoe_description">Shoe Description</label>
                                            <textarea name="shoe_description" class="form-control" required><?php echo htmlspecialchars($shoe['ShoeDescription']); ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="quantity">Quantity Available</label>
                                            <!-- <input type="number" name -->
                                            <input type="number" name="quantity" class="form-control" value="<?php echo htmlspecialchars($shoe['QuantityAvailable']); ?>" required min="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="price">Price</label>
                                            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($shoe['Price']); ?>" required min="0">
                                        </div>
                                        <div class="form-group">
                                            <label for="size">Size</label>
                                            <input type="text" name="size" class="form-control" value="<?php echo htmlspecialchars($shoe['Size']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="color">Color</label>
                                            <input type="text" name="color" class="form-control" value="<?php echo htmlspecialchars($shoe['Color']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" name="edit" class="btn btn-primary">Update Shoe</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No shoes available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
