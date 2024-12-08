<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <style>
        /* Simple chat UI styles */
        body {
            font-family: Arial, sans-serif;
        }
        #chat-container {
            width: 60%;
            margin: 0 auto;
            padding-top: 50px;
        }
        #messages {
            height: 300px;
            overflow-y: scroll;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message {
            margin-bottom: 10px;
        }
        .message .sender {
            font-weight: bold;
        }
        .message .content {
            margin-left: 20px;
        }
        input[type="text"] {
            width: 80%;
            padding: 8px;
            margin-right: 10px;
        }
        button {
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    <div id="chat-container">
        <h2>Chat with Users</h2>
        <div>
            <select id="user-selector">
                <option value="">Select a User</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div id="messages"></div>
        <input type="text" id="message-input" placeholder="Type a message" />
        <button id="send-message">Send</button>
    </div>

    <script>
        // Initialize Pusher
        Pusher.logToConsole = true;
        const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}'
        });
        console.log(`Pusher initialized with key: {{ env('PUSHER_APP_KEY') }} and cluster: {{ env('PUSHER_APP_CLUSTER') }}`);
        console.log(pusher);
        let channel;

        // Handle user selection
        document.getElementById('user-selector').addEventListener('change', function() {
            const receiverId = this.value;
            if (receiverId) {
                // Subscribe to a private channel for the selected user
                if (channel) {
                    channel.unsubscribe();
                }
                channel = pusher.subscribe('chat.' + receiverId);
                channel.bind('MessageSent', function(data) {
                    displayMessage(data);
                });
                loadMessages(receiverId);
            }
        });

        // Fetch messages from the server
        function loadMessages(receiverId) {
            fetch(`/messages/${receiverId}`)
                .then(response => response.json())
                .then(messages => {
                    document.getElementById('messages').innerHTML = '';
                    messages.forEach(message => {
                        displayMessage(message);
                    });
                });
        }

        // Display message in the chat
        function displayMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('message');
            messageDiv.innerHTML = `
                <span class="sender">${message.sender_id}</span>: 
                <span class="content">${message.content}</span>
            `;
            document.getElementById('messages').appendChild(messageDiv);
        }

        // Send message to the server
        document.getElementById('send-message').addEventListener('click', function() {
            const receiverId = document.getElementById('user-selector').value;
            const content = document.getElementById('message-input').value;
            if (receiverId && content) {
                fetch('/messages/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        receiver_id: receiverId,
                        content: content
                    })
                })
                .then(response => response.json())
                .then(message => {
                    displayMessage(message);
                    document.getElementById('message-input').value = ''; // clear input
                });
            }
        });
    </script>
</body>
</html>
