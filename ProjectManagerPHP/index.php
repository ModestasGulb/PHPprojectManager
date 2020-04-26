<html>

<head>
    <link rel="stylesheet" href="Style.css">
    <title>PHP project manager</title>
</head>

<?php

// Navbar
echo "<body>";
echo "<header><nav><div>";
echo "<form action='index.php' method='POST'><input type='submit' name='Employees' value='Employees'></form>";
echo "<form action='index.php' method='POST'><input type='submit' name='Projects' value='Projects'></form></div><h1>Project manager</h1>";
echo "</nav></header>";


// Connection to server

$servername = "localhost";
$username = "root";
$password = "mysql";
$dbname = "projectmanagerphp";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "<br><p class='systemMsgPositive'>Connected successfully</p><br>";

// Create employee logic

if (isset($_POST['createEmployee'])) {
    if ($_POST['createEmployee'] == "") {
        echo "<p class='systemMsgNegative'>Employee&#x27s name was not entered</p><br>";
    } else {
        $createEmployee = $_POST['createEmployee'];
        $stmt = $conn->prepare("INSERT INTO employees (Name) VALUES (?) ");
        $stmt->bind_param("s", $createEmployee);
        $stmt->execute();
        $stmt->close();
    }
}

// Create project logic

if (isset($_POST['createProject'])) {
    if ($_POST['createProject'] == "") {
        echo "<p class='systemMsgNegative'>Project&#x27s name was not entered</p><br>";
    } else {
        $createProject = $_POST['createProject'];
        $stmt = $conn->prepare("INSERT INTO projects (Project) VALUES (?) ");
        $stmt->bind_param("s", $createProject);
        $stmt->execute();
        $stmt->close();
    }
}

// Update employee logic

if (isset($_POST['oldEmployeeData']) && isset($_POST['newEmployeeData'])) {
    if ($_POST['assignProject'] == "NULL") {
        $stmt = $conn->prepare("DELETE FROM employees WHERE ID = ?");
        $stmt->bind_param("i", $_POST['oldEmployeeData']);
        $stmt->execute();
        $stmt->close();

        $EmployeeName = $_POST['newEmployeeData'];
        if ($_POST['newEmployeeData'] == "") {
            $EmployeeName = $_POST['oldEmployeeName'];
        }
        $stmt = $conn->prepare("INSERT INTO employees (ID, Name) VALUES (?, ?)");
        $stmt->bind_param("is", $_POST['oldEmployeeData'], $EmployeeName);
        $stmt->execute();
        $stmt->close();
    } else {
        if ($_POST['newEmployeeData'] == "") {
            $EmployeeName = $_POST['oldEmployeeName'];
        } else {
            $EmployeeName = $_POST['newEmployeeData'];
        }
        $assignProject = $_POST['assignProject'];
        $stmt = $conn->prepare("UPDATE employees SET Project_ID = ?, Name = ?  WHERE ID = ?");
        $stmt->bind_param("isi", $assignProject, $EmployeeName, $_POST['oldEmployeeData']);
        $stmt->execute();
        $stmt->close();
    }
}

// Update project logic
if (isset($_POST['oldProjectData']) && isset($_POST['newProjectData'])) {
    if ($_POST['newProjectData'] == "") {
        $ProjectName = $_POST['oldProjectName'];
    } else {
        $ProjectName = $_POST['newProjectData'];
    }
    $stmt = $conn->prepare("UPDATE projects SET Project = ? WHERE ID = ?");
    $stmt->bind_param("si", $ProjectName, $_POST['oldProjectData']);
    $stmt->execute();
    $stmt->close();
}

// Delete logic

// Delete employee

if (isset($_POST['employeeToDelete'])) {
    $stmt = $conn->prepare("DELETE FROM employees WHERE ID = ?");
    $stmt->bind_param("i", $_POST['employeeToDelete']);
    $stmt->execute();
    $stmt->close();
}

// Delete project

if (isset($_POST['projectToDelete'])) {
    $stmt = $conn->prepare("DELETE FROM projects WHERE ID = ?");
    $stmt->bind_param("i", $_POST['projectToDelete']);
    $stmt->execute();
    $stmt->close();
}

// Display table

if (
    empty($_POST) || isset($_POST['Employees']) || isset($_POST['employeeToUpdate']) ||
    isset($_POST['newEmployeeData']) || isset($_POST['employeeToDelete']) || isset($_POST['createEmployee'])
) {

    // Display employees table

    $stmt = $conn->prepare("SELECT employees.ID, Name, Project 
        FROM employees LEFT JOIN projects ON employees.Project_ID = projects.ID  WHERE name LIKE ?");
    $stmt->bind_param("s", $a = "%%");
    $stmt->execute();
    $stmt->bind_result($id, $name, $project);

    echo "<table><tr><th>ID</th><th>Name</th><th>Project</th><th>Actions</th></tr>";
    while ($stmt->fetch()) {
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$name</td>";
        echo "<td>$project</td>";
        echo "<td><form method='POST'><input type='submit' value='Delete'>
            <input type='hidden' name='employeeToDelete' value='" . $id . "'></form>
            <form method='POST'><input type='submit' value='Update'>
            <input type='hidden' name='employeeToUpdate' value='" . $id . "'></form></td>";
        echo "</tr>";
    }

    echo "</table>";
    $stmt->close();

    // Display create employee

    if (!isset($_POST['employeeToUpdate'])) {
        echo "<footer><form method='POST'><input type='text' name='createEmployee' placeholder='Employee&#x27;s name'>
            <input type='submit' value='Add employee'></form></footer>";
    }
} else {

    // Display project  table

    $stmt = $conn->prepare("SELECT projects.ID, projects.Project, group_concat(Name SEPARATOR ', ') AS Employees 
       FROM employees RIGHT JOIN projects
       ON employees.Project_ID = projects.ID
       WHERE projects.Project LIKE ?
       GROUP BY projects.Project, projects.ID
       ORDER BY ID");
    $stmt->bind_param("s", $a = "%%");
    $stmt->execute();
    $stmt->bind_result($id, $project, $employees);

    echo "<table><tr><th>ID</th><th>Name</th><th>Project</th><th>Actions</th></tr>";
    while ($stmt->fetch()) {
        echo "<tr>";
        echo "<td>$id</td>";
        echo "<td>$project</td>";
        echo "<td>$employees</td>";
        echo "<td><form method='POST'><input type='submit' value='Delete'>
            <input type='hidden' name='projectToDelete' value='" . $id . "'></form>
            <form method='POST'><input type='submit' value='Update'>
            <input type='hidden' name='projectToUpdate' value='" . $id . "'></form></td>";
        echo "</tr>";
    }

    echo "</table>";
    $stmt->close();

    // Display create project

    if (!isset($_POST['projectToUpdate'])) {
        echo "<footer><form method='POST'><input type='text' name='createProject' placeholder='Project&#x27;s name'>
            <input type='submit' value='Add project'></form></footer>";
    }
}

// Display update fields

// Employee update field

if (isset($_POST['employeeToUpdate'])) {
    $stmt = $conn->prepare("SELECT employees.ID, Name, Project, Project_ID 
        FROM employees LEFT JOIN projects ON employees.Project_ID = projects.ID  WHERE employees.ID = ?");
    $stmt->bind_param("i", $_POST['employeeToUpdate']);
    $stmt->execute();
    $stmt->bind_result($id, $name, $project, $project_ID);
    echo "<footer>";
    while ($stmt->fetch()) {
        echo "<form method='POST' id='employeeUpdate'><label for='newProjectName'>Enter new employee name:</label>
        <input type='text' name='newEmployeeData' placeholder='$name'><input type='submit' value='Update'>
        <input type='hidden' name='oldEmployeeData' value='" . $_POST['employeeToUpdate'] . "'>
        <input type='hidden' name='oldEmployeeName' value='$name'>";
    }
    $stmt->close();

    // Drop down project selection

    echo "<select name='assignProject' form='employeeUpdate'>";
    $sql = "SELECT ID, Project FROM projects";
    $result = $conn->query($sql);
    $null = false;
    if ($result->num_rows > 0) {
        // output data of each row
        while ($row = $result->fetch_assoc()) {
            if ($project == null && $null == false) {
                $null = true;
            }
            if ($project == $row["Project"]) {
                echo "<option value='" . $row["ID"] . "' selected>" . $row["Project"] . "</option>";
            } else {
                echo "<option value='" . $row["ID"]  . "'>" . $row["Project"] . "</option>";
            }
        }
    } else {
        echo "0 results";
    }
    if ($null == false) {
        echo "<option value=NULL></option>";
    } else {
        echo "<option value=NULL selected></option>";
    }
    echo "</select></form></footer>";
}

// Project update field

if (isset($_POST['projectToUpdate'])) {
    $stmt = $conn->prepare("SELECT * FROM projects WHERE ID = ?");
    $stmt->bind_param("i", $_POST['projectToUpdate']);
    $stmt->execute();
    $stmt->bind_result($id, $project);

    echo "<footer>";

    while ($stmt->fetch()) {
        echo "<form method='POST'><label for='newProjectName'>Enter new project name:</label>
        <input type='text' name='newProjectData' placeholder='" . $project . "'><input type='submit' value='Update'>
        <input type='hidden' name='oldProjectData' value='" . $_POST['projectToUpdate'] . "'>
        <input type='hidden' name='oldProjectName' value='$project'></form>";
    }
}

mysqli_close($conn);
echo "</footer>";
echo "</body>";
?>


</html>