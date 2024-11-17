<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP AI</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
        }

        /* Main container that holds both the button container and the response text */
        .main-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100%;
        }

        /* Container that holds the button and title */
        .container {
            text-align: center;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 300px;
            box-sizing: border-box;
            margin-bottom: 10px;
        }

        /* Style for the button */
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.5em;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            outline: none;
        }

        button.recording {
            background-color: #f44336;
        }

        button i {
            font-size: 1.5em;
        }

        button:hover:enabled {
            background-color: #45a049;
        }

        button:focus {
            outline: none;
        }

        /* Style for the audio */
        audio {
            margin-top: 20px;
            width: 100%;
            border-radius: 5px;
        }

        .response-container {
            margin: 20px auto; 
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            font-family: monospace;
            font-size: 18px;
            max-width: 80%;
            min-width: 300px;
            max-height: 400px;
            overflow-y: auto; 
            overflow-x: hidden;
            white-space: pre-wrap;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: left;
        }

        /* Style to allow width control and height expansion according to text content */
        @media screen and (max-width: 600px) {
            #response-text {
                font-size: 2em; 
                max-width: 90%;
            }
        }

        /* Style for the proceed button */
        #proceed-button {
            width: 100px;
            height: 50px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            outline: none;
        }

        #proceed-button:hover:enabled {
            background-color: #45a049;
        }

        #proceed-button:focus {
            outline: none;
        }

        #proceed-button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Style for the loader */
        .loader {
            border: 8px solid #f3f3f3;
            border-top: 8px solid #3498db;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="container">
            <h1>ERP AI AGENT</h1>
            <button id="record-button" class="record-btn">
                <i id="record-icon" class="fas fa-microphone"></i>
            </button>
        </div>

        <div id="loader" class="loader" style="display: none;"></div>

        <div id="audio-response" class="response-container"></div>
        <div id="text-response" class="response-container"></div>
        
        <div id="validation-container" style="display: none;">
            <p>If all the fields are valid then proceed or try again</p>
            <button id="proceed-button" onclick="validateAndProceed()">Proceed</button>
        </div>
    </div>

    <script>
        const recordButton = document.getElementById('record-button');
        const recordIcon = document.getElementById('record-icon');
        const loader = document.getElementById('loader');
        const audioResponseDiv = document.getElementById('audio-response');
        const textResponseDiv = document.getElementById('text-response');
        const validationContainer = document.getElementById('validation-container');
        let mediaRecorder;
        let audioChunks = [];
        let mediaStream;
        let isRecording = false;

        recordButton.addEventListener('click', async () => {
            if (isRecording) {
                mediaRecorder.stop();
                mediaStream.getTracks().forEach(track => track.stop());
                recordButton.classList.remove('recording');
                recordIcon.classList.remove('fa-stop');
                recordIcon.classList.add('fa-microphone');
                isRecording = false;
            } else {
                audioChunks = [];
                mediaStream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(mediaStream);

                mediaRecorder.ondataavailable = event => {
                    audioChunks.push(event.data);
                };

                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                    const formData = new FormData();
                    formData.append('audio', audioBlob, 'recording.wav');

                    // Show the loader while waiting for the response
                    loader.style.display = 'block';
                    validationContainer.style.display = 'none'; // Hide validation while loading

                    fetch('/', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Hide the loader once response is received
                        loader.style.display = 'none';

                        audioResponseDiv.textContent = `Voice Command: ${data.audioResponse}`;
                        textResponseDiv.textContent = `Project Name: ${data.textResponseName}\nProject Purpose: ${data.textResponsePurpose}\nProject Cost: ${data.textResponseCost}`;

                        // Show validation container after displaying the results
                        validationContainer.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error uploading audio:', error);
                        loader.style.display = 'none';
                    });
                };

                mediaRecorder.start();
                recordButton.classList.add('recording');
                recordIcon.classList.remove('fa-microphone');
                recordIcon.classList.add('fa-stop');
                isRecording = true;
            }
        });

        function validateAndProceed() {
            alert('Proceeding with validated data!');
        }
    </script>
</body>
</html>
