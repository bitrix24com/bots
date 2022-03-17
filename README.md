# Where to find Bitrix24 bot-related documentation? #

The documentation is available at: https://training.bitrix24.com/support/training/course/index.php?COURSE_ID=115&INDEX=Y

### What are contents of this repository? ###

Contains three completed chatbots:
* EchoBot: bot repeats incoming messages and supports all key platform features.
* ITR Bot: bot for Open Channels, with multi-level menu constructor available in the code.
* ServiceBot: service bot for chats. This bot type tracks all messages in a chat.

### How should I update the bot? ###

*First, you need to understand what do you want to do with this bot :)*

When this bot responds only when initiated by users:
* In basic version, no changes needed
* However, you can think about security and change methods for storage of logs, cache and app settings

*When this bot sometimes may initiate contact with users:*
* Change constants CLIENT_ID and CLIENT_SECRET to be issued to you inside partner account or in local applications
* you can also ensure security and change storage methods for message logs, cache and app settings  

### Attention ###

Bots are presented for informational purposes only and you can use them in your projects. However, you are fully responsible for operation of your applications.
