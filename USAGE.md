To listen and respond to messages you must add a messagebroker.php to
the root directory of your plugin and have it implement the one-to-one
callback for build_message_receivers.
It is expected that this return a list of instantiated message receivers.

Message receiver:
A class that can receive and process a message.
Implements the tool_messagebroker\receiver\message_receiver interface.