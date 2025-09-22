    <!-- Focused Message Full View -->
    <div class="flex-1  min-h-screen">
      <div class="relative group inline-block mb-1">
        <a href="messages.php?view=inbox-admin" class="block rounded-full p-2 hover:bg-emerald-100 hover:scale-110 transition-transform duration-200">
          <img src="/assets/img/back-icon.png" alt="Back" class="w-4 h-4" />
        </a>
        <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 pointer-events-none z-10 transition duration-200">
          Back to inbox
        </div>
      </div>

      <div class="bg-white p-6 rounded shadow">
        <div class="flex text-2xl gap-x-1 font-semibold text-emerald-700 mb-2">
          <p>Subject:</p>
          <p>
            <?= isset($focusedMessage['subject']) && trim($focusedMessage['subject']) !== ''
              ? htmlspecialchars($focusedMessage['subject'])
              : 'None' ?>
          </p>
        </div>

        <div class="flex justify-between text-sm text-gray-500 mb-1">
          <div>
            <strong>From:</strong> <?= htmlspecialchars($focusedMessage['sender_name']) ?> |
            <strong>Date:</strong> <?= date('M d, Y H:i', strtotime($focusedMessage['created_at'])) ?>
          </div>

          <!-- Actionbar Navigation -->
          <div class="flex justify-end items-center gap-x-2">
            <!-- Reply Icon with Tooltip -->
            <div class="relative group">
              <a href="messages.php?reply_to_id=<?= $focusedMessage['id'] ?>" class="block rounded-full p-2 hover:bg-emerald-100  hover:scale-110 transition-transform duration-200">
                <img src="/assets/img/reply-icon.png" alt="Reply" class="w-4 h-4" />
              </a>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Reply
              </div>
            </div>

            <!-- Delete Icon with Tooltip -->
            <div class="relative group">
              <form method="POST" action="/actions/message/delete-message.php">
                <input type="hidden" name="message_id" value="<?= $focusedMessage['id'] ?>">
                <button type="submit" class="rounded-full p-2 hover:bg-emerald-100 duration-200 cursor-pointer hover:scale-110 transition-transform">
                  <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
                </button>
              </form>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-700 font-semibold text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Delete
              </div>
            </div>
          </div>
        </div>

        <p class="text-gray-800 whitespace-pre-line mb-4">
          <?= htmlspecialchars($focusedMessage['content']) ?>
        </p>


      </div>
    </div>