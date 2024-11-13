<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP AI</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
</head>
<body>

    <div class="main-container">
        <div class="container">
            <h1>ERP AI AGENT</h1>
            <button id="record-button" class="record-btn">
                <i id="record-icon" class="fas fa-microphone"></i>
            </button>
        </div>

        <!-- Placeholders for separate responses -->
        <div id="audio-response" class="response-container"></div>
        <div id="text-response" class="response-container"></div>
    </div>

    <script>
        const recordButton = document.getElementById('record-button');
        const recordIcon = document.getElementById('record-icon');
        const audioResponseDiv = document.getElementById('audio-response');
        const textResponseDiv = document.getElementById('text-response');
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

                    fetch('/', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Display each response in its respective div
                        audioResponseDiv.textContent = `Voice Command: ${data.audioResponse}`;
                        textResponseDiv.textContent = `Project Name: ${data.textResponseName}\nProject Purpose: ${data.textResponsePurpose}\nProject Cost: ${data.textResponseCost}`;
                    })
                    .catch(error => console.error('Error uploading audio:', error));
                };

                mediaRecorder.start();
                recordButton.classList.add('recording');
                recordIcon.classList.remove('fa-microphone');
                recordIcon.classList.add('fa-stop');
                isRecording = true;
            }
        });
    </script>
</body>
</html>
