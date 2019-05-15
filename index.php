<?php
require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=ujicobastorage;AccountKey=mWDjM0vX4byAlWWkC+Jm6Gi4fyQcb4UISXKPvadrfIhvqSZk/XvXYqEmSPIpJ0F9PQqbT5+QHWcYfG4bytzCew==;EndpointSuffix=core.windows.net";

// Create blob client.
$blobClient = BlobRestProxy::createBlobService($connectionString);

$url;

if (isset($_POST['submit'])) {

    $containerName = "images";
    $fileToUpload = strtolower($_FILES["fileToUpload"]["name"]);
    try {
    //     $createContainerOptions = new CreateContainerOptions();

    //     $createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);

    // // Set container metadata.
    // $createContainerOptions->addMetaData("key1", "value1");
    // $createContainerOptions->addMetaData("key2", "value2");


      // $blobClient->createContainer($containerName, $createContainerOptions);
        // Getting local file so that we can upload it to Azure
        $myfile = fopen($_FILES["fileToUpload"]["tmp_name"], "r") or die("Unable to open file!");
        fclose($myfile);
        
        # Upload file as a block blob
        echo "Uploading BlockBlob: ".PHP_EOL;
        echo $fileToUpload;
        echo "<br />";
        
        $content = fopen($fileToUpload, "r");

        //Upload blob
        $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

        // List blobs.
        $listBlobsOptions = new ListBlobsOptions();
        $listBlobsOptions->setPrefix("");

        echo "These are the blobs present in the container: ";

        do{
            $result = $blobClient->listBlobs($containerName, $listBlobsOptions);
            foreach ($result->getBlobs() as $blob)
            {
                echo $blob->getName().": ".$blob->getUrl()."<br />";
                $url = $blob->getUrl();
            }
        
            $listBlobsOptions->setContinuationToken($result->getContinuationToken());
        } while($result->getContinuationToken());
        echo "<br />";

        // Get blob.
        echo "This is the content of the blob uploaded: ";
        $blob = $blobClient->getBlob($containerName, $fileToUpload);
        // $url = $blob->getUrl();
        $url = "https://ujicobastorage.blob.core.windows.net/$containerName/$fileToUpload";
        echo "<br />";
    }
    catch(ServiceException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }
    catch(InvalidArgumentTypeException $e){
        // Handle exception based on error codes and messages.
        // Error codes and messages are here:
        // http://msdn.microsoft.com/library/azure/dd179439.aspx
        $code = $e->getCode();
        $error_message = $e->getMessage();
        echo $code.": ".$error_message."<br />";
    }

    $fileToUpload = strtolower($_FILES["fileToUpload"]["name"]);
    $content = fopen($_FILES["fileToUpload"]["tmp_name"], "r");
    // echo fread($content, filesize($fileToUpload));
    $blobClient->createBlockBlob($containerName, $fileToUpload, $content);

    header("Location: index.php?img=$url");
}
?>

<!DOCTYPE html>
<html>
 <head>
 <meta charset="utf-8">
    <title>Analisis Foto</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <link href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" rel="stylesheet">

  </head>
  <script type="text/javascript">
    function processImage() {
        // **********************************************
        // *** Update or verify the following values. ***
        // **********************************************
 
        // Replace <Subscription Key> with your valid subscription key.
        var subscriptionKey = "063cc9d9f45842c283031f074d68bf8b";
 
        // You must use the same Azure region in your REST API method as you used to
        // get your subscription keys. For example, if you got your subscription keys
        // from the West US region, replace "westcentralus" in the URL
        // below with "westus".
        //
        // Free trial subscription keys are generated in the "westus" region.
        // If you use a free trial subscription key, you shouldn't need to change
        // this region.
        var uriBase =
            "https://southeastasia.api.cognitive.microsoft.com/vision/v2.0/analyze";
 
        // Request parameters.
        var params = {
            "visualFeatures": "Categories,Description,Color",
            "details": "",
            "language": "en",
        };
 
        // Display the image.
        var sourceImageUrl = document.getElementById("inputImage").value;
        document.querySelector("#sourceImage").src = sourceImageUrl;
 
        // Make the REST API call.
        $.ajax({
            url: uriBase + "?" + $.param(params),
 
            // Request headers.
            beforeSend: function(xhrObj){
                xhrObj.setRequestHeader("Content-Type","application/json");
                xhrObj.setRequestHeader(
                    "Ocp-Apim-Subscription-Key", subscriptionKey);
            },
 
            type: "POST",
 
            // Request body.
            data: '{"url": ' + '"' + sourceImageUrl + '"}',
        })
 
        .done(function(data) {
            // Show formatted JSON on webpage.
            $("#responseTextArea").val(JSON.stringify(data, null, 2));
        })
 
        .fail(function(jqXHR, textStatus, errorThrown) {
            // Display error message.
            var errorString = (errorThrown === "") ? "Error. " :
                errorThrown + " (" + jqXHR.status + "): ";
            errorString += (jqXHR.responseText === "") ? "" :
                jQuery.parseJSON(jqXHR.responseText).message;
            alert(errorString);
        });
    };
</script>
<body>
    <div class="container">
            <div class="jumbotron"> <br>
                <h1>Upload Foto</h1>
            </div>
        <div class="mt-4 mb-2">
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input type="file" name="fileToUpload" accept=".jpeg,.jpg,.png" required>
                <input type="submit" name="submit" value="Upload">
            </form>
        </div>
        <br>
        <br>
    
<br>
<br> 
<input type="hidden" name="inputImage" id="inputImage" value="<?php echo $_GET['img'];?>" />
<button onclick="processImage()">Analyze image</button>


<br>
<br>
<br>
<div id="wrapper" style="width:1020px; display:table;">
    <div id="jsonOutput" style="width:600px; display:table-cell;">
        Response:
        <br><br>
        <textarea id="responseTextArea" class="UIInput"
                  style="width:580px; height:400px;"></textarea>
    </div>
    <div id="imageDiv" style="width:420px; display:table-cell;">
        Source image:
        <br><br>
        <img id="sourceImage" src="<?php echo $_GET['img'];?>" width="400" />
    </div>
</div>
</div>
  </body>
</html>
