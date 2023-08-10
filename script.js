jQuery(document).ready(function($) {
    var currentButtonIndex = 0;
    var now = new Date();
    var currentHour = now.getHours();
    var currentMinute = now.getMinutes();
    var buttons = $('.whatsapp-chat-rotator .whatsapp-chat-button');

    // Function to toggle WhatsApp numbers
    function toggleWhatsAppNumbers() {
        buttons.hide(); // Hide all buttons

        while (true) {
            var button = buttons.eq(currentButtonIndex);
            var onlineTime = button.data('online-time');
            var offlineTime = button.data('offline-time');

            var [onlineHour, onlineMinute] = onlineTime.split(':').map(Number);
            var [offlineHour, offlineMinute] = offlineTime.split(':').map(Number);

            if (
                (currentHour > onlineHour || (currentHour === onlineHour && currentMinute >= onlineMinute)) &&
                (currentHour < offlineHour || (currentHour === offlineHour && currentMinute < offlineMinute))
            ) {
                button.show(); // Show the button if within the time range
                break; // Showed a button, exit the loop
            }

            currentButtonIndex = (currentButtonIndex + 1) % buttons.length;

            if (currentButtonIndex === 0) {
                break; // Break the loop after checking all buttons
            }
        }
    }

    // Apply custom CSS to WhatsApp Chat Button

    // Add click event to WhatsApp Chat Button
    $(document).on('click', '.whatsapp-chat-button:visible', function(e) {
        e.preventDefault();
        currentButtonIndex = (currentButtonIndex + 1) % buttons.length;
        toggleWhatsAppNumbers();
        var activeButton = $('.whatsapp-chat-rotator .whatsapp-chat-button:visible');
        var message = activeButton.data('whatsapp-text') || "Saya ingin bertanya"; // Default message if attribute is not set
        var encodedMessage = encodeURIComponent(message);
        var number = activeButton.data('number');
        var url = 'https://api.whatsapp.com/send?phone=' + number + '&text=' + encodedMessage;
        window.open(url, '_blank');
    });

    // Initial call to show the buttons within the time range
    toggleWhatsAppNumbers();
});
