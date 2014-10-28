<?php

try {

$loader = new \Phalcon\Loader();
    $loader->registerDirs(array(
        __DIR__.'/models/'
    ))->register();

$di = new \Phalcon\DI\FactoryDefault();

// Set up the database service
$di->set('db', function(){
    return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
        "host" => "localhost",
        "username" => "root",
        "password" => "password",
        "dbname" => "robotics"
    ));
});

// Create and bind the DI to the application
$app = new \Phalcon\Mvc\Micro($di);

// Define the routes here

// Load Web app
$app->get('/', function() {

    // Create a response
    $response = new Phalcon\Http\Response();

    // Set the Content-type header
    $response->setContentType('text/html');

    // Pass the content of a file
    $response->setContent(file_get_contents("webapp.html"));

    // Return response
    return $response;
});

// Retrieves all robots
$app->get('/api/robots', function() use ($app) {

    $phql = "SELECT * FROM Robots ORDER BY name";
    $robots = $app->modelsManager->executeQuery($phql);

    $data = array();
    foreach ($robots as $robot) {
        $data[] = array(
            'id' => $robot->id,
            'name' => $robot->name,
            'type' => $robot->type,
            'year' => $robot->year
        );
    }

    echo json_encode($data);
});
// Searches for robots with $name in their name
$app->get('/api/robots/search/{name}', function ($name) use ($app) {

    $phql = "SELECT * FROM Robots WHERE name LIKE :name: ORDER BY name";
    $robots = $app->modelsManager->executeQuery($phql, array(
        'name' => '%' . $name . '%'
    ));

    $data = array();
    foreach ($robots as $robot) {
        $data[] = array(
            'id' => $robot->id,
            'name' => $robot->name,
            'type' => $robot->type,
            'year' => $robot->year
        );
    }
    echo json_encode($data);
});
// Retrieves robots based on primary key
$app->get('/api/robots/{id:[0-9]+}', function ($id) use ($app) {

    $phql = "SELECT * FROM  Robots WHERE id = :id:";
    $robot = $app->modelsManager->executeQuery($phql, array(
        'id' => $id
    ))->getFirst();

    // Create a response
    $response = new Phalcon\Http\Response();

    if ($robot == false) {
        $response->setJsonContent(array('status' => 'NOT-FOUND'));
    } else {
        $response->setJsonContent(array(
            'status' => 'FOUND',
            'id' => $robot->id,
            'name' => $robot->name,
            'type' => $robot->type,
            'year' => $robot->year
            )
        );
    }
    return $response;
});
// Adds a new robot
$app->post('/api/robots', function() use ($app) {

    $robot = $app->request->getJsonRawBody();

    $phql = "INSERT INTO Robots (name, type, year) VALUES (:name:, :type:, :year:)";

    $status = $app->modelsManager->executeQuery($phql, array(
        'name' => $robot->name,
        'type' => $robot->type,
        'year' => $robot->year
    ));

    // Create a response
    $response = new Phalcon\Http\Response();

    // Check if the insertion was successful
    if ($status->success() == true) {
        $robot->id = $status->getModel()->id;
        //instead of array of $robot should be obj $robot
        $response->setJsonContent(array('status' => 'OK', 'id' => $robot->id));

    } else {
        // Change the HTTP status
        //$response->setStatusCode(500, "Internal Error");
        $response->setStatusCode(500, "Internal Server Error");
        // Send errors to the client
        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(array('status' => 'ERROR', 'message' => $errors));
    }

    return $response;
});
// Updates robots based on primary key
$app->put('/api/robots/{id:[0-9]+}', function($id) use ($app) {

    $robot = $app->request->getJsonRawBody();

    $phql = "UPDATE Robots SET name = :name:, type = :type:, year = :year: WHERE id = :id:";
    $status = $app->modelsManager->executeQuery($phql, array(
        'id' => $id,
        'name' => $robot->name,
        'type' => $robot->type,
        'year' => $robot->year
    ));

    // Create a response
    $response = new Phalcon\Http\Response();

    // Check if the insertion was successful
    if ($status->success() == true) {
        $response->setJsonContent(array('status' => 'OK'));
    } else {

        // Change the HTTP status
        $response->setStatusCode(500, "Internal Error");

        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors[] = $message->getMessage();
        }
        $response->setJsonContent(array('status' => 'ERROR', 'messages' => $errors));
    }
    return $response;
});
// Deletes robots based on primary key
$app->delete('/api/robots/{id:[0-9]+}', function($id) use ($app) {

    $phql = "DELETE FROM Robots WHERE id = :id:";
    $status = $app->modelsManager->executeQuery($phql, array(
        'id' => $id
    ));

    //Create a response
    $response = new Phalcon\Http\Response();

    if ($status->success() == true) {
        $response->setJsonContent(array('status' => 'OK'));
    } else {

        // Change the Http status
        $response->setStatusCode(500, "Internal Error");
        $errors = array();
        foreach ($status->getMessages() as $message) {
            $errors = array();
            $errors[] = $message->getMessage();
        }

        $response->setJsonContent(array(
            'status' => 'ERROR',
            'messages' => $errors
        ));
    }

    return $response;
});

$app->handle();

} catch (\Phalcon\Exception $e) {
    echo "PhalconException: ", $e->getMessage();
}

