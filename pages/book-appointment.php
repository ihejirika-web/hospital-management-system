<?php

// Show PHP errors while we are testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to database
require_once "../config/database.php";

// Only process submitted forms
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get form information
    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $doctor = $_POST["doctor"];
    $date = $_POST["date"];
    $time = $_POST["time"];
    $reason = $_POST["reason"];


    // --------------------------------
    // 1. Find or create the patient
    // --------------------------------

    $patientQuery = $conn->prepare(
        "SELECT id FROM patients WHERE email = ?"
    );

    $patientQuery->bind_param("s", $email);
    $patientQuery->execute();

    $patientResult = $patientQuery->get_result();

    if ($patientResult->num_rows > 0) {

        $patient = $patientResult->fetch_assoc();
        $patient_id = $patient["id"];

    } else {

        // Temporary password for our test patient
        $password = password_hash("123456", PASSWORD_DEFAULT);

        $patientInsert = $conn->prepare(
            "INSERT INTO patients 
            (full_name, email, phone, password)
            VALUES (?, ?, ?, ?)"
        );

        $patientInsert->bind_param(
            "ssss",
            $name,
            $email,
            $phone,
            $password
        );

        $patientInsert->execute();

        $patient_id = $conn->insert_id;
    }


    // --------------------------------
    // 2. Find the selected doctor
    // --------------------------------

    $doctorNames = [
        "john-smith" => "Dr. John Smith",
        "sarah-johnson" => "Dr. Sarah Johnson",
        "michael-brown" => "Dr. Michael Brown"
    ];

    $doctorSpecialties = [
        "john-smith" => "General Physician",
        "sarah-johnson" => "Cardiologist",
        "michael-brown" => "Dermatologist"
    ];

    $doctorName = $doctorNames[$doctor];
    $specialty = $doctorSpecialties[$doctor];


    $doctorQuery = $conn->prepare(
        "SELECT id FROM doctors WHERE full_name = ?"
    );

    $doctorQuery->bind_param("s", $doctorName);
    $doctorQuery->execute();

    $doctorResult = $doctorQuery->get_result();

    if ($doctorResult->num_rows > 0) {

        $doctorData = $doctorResult->fetch_assoc();
        $doctor_id = $doctorData["id"];

    } else {

        // Create doctor for our first test
        $doctorEmail = strtolower(
            str_replace(" ", ".", $doctorName)
        ) . "@medicare.com";

        $doctorPhone = "08000000000";
        $doctorPassword = password_hash("123456", PASSWORD_DEFAULT);

        $doctorInsert = $conn->prepare(
            "INSERT INTO doctors
            (full_name, specialty, email, phone, password)
            VALUES (?, ?, ?, ?, ?)"
        );

        $doctorInsert->bind_param(
            "sssss",
            $doctorName,
            $specialty,
            $doctorEmail,
            $doctorPhone,
            $doctorPassword
        );

        $doctorInsert->execute();

        $doctor_id = $conn->insert_id;
    }


    // --------------------------------
    // 3. Save the appointment
    // --------------------------------

    $appointmentInsert = $conn->prepare(
        "INSERT INTO appointments
        (patient_id, doctor_id, appointment_date, appointment_time, reason, status)
        VALUES (?, ?, ?, ?, ?, 'Pending')"
    );

    $appointmentInsert->bind_param(
        "iisss",
        $patient_id,
        $doctor_id,
        $date,
        $time,
        $reason
    );


    if ($appointmentInsert->execute()) {

        echo "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <title>Appointment Successful</title>

            <style>
                body {
                    font-family: Arial, sans-serif;
                    background: #f5f9fc;
                    text-align: center;
                    padding: 100px 20px;
                }

                .success-box {
                    max-width: 600px;
                    margin: auto;
                    background: white;
                    padding: 50px;
                    border-radius: 12px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                }

                h1 {
                    color: #0077cc;
                }

                p {
                    color: #555;
                    line-height: 1.6;
                }

                a {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 12px 25px;
                    background: #0077cc;
                    color: white;
                    text-decoration: none;
                    border-radius: 6px;
                }

                a:hover {
                    background: #005fa3;
                }
            </style>
        </head>

        <body>

            <div class='success-box'>

                <h1>✓ Appointment Booked Successfully!</h1>

                <p>
                    Thank you, <strong>$name</strong>.
                </p>

                <p>
                    Your appointment with
                    <strong>$doctorName</strong>
                    has been submitted successfully.
                </p>

                <p>
                    Date: <strong>$date</strong>
                    <br>
                    Time: <strong>$time</strong>
                </p>

                <p>
                    Your appointment is currently
                    <strong>Pending</strong>.
                </p>

                <a href='../index.php'>
                    Back to Home
                </a>

            </div>

        </body>
        </html>
        ";

    } else {

        echo "Appointment Error: " . $conn->error;
    }

} else {

    echo "Invalid request. Please submit the appointment form.";

}

?>