<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/head.php';
renderHead('Feedback Form', true);
?>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col ">
  <!-- Header Section -->
  <header class="bg-emerald-950 shadow-md sticky top-0 z-10 p-2">
    <section class="max-w-6xl mx-auto flex items-center justify-between">
      <!-- Logo + Title -->
      <div class="flex items-center gap-4">
        <img src="/assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-14 w-14 border rounded-full bg-white">
        <p class="text-xl md:text-3xl font-medium text-white">BESIMS</p>
      </div>

      <!-- Mobile Menu Toggle -->
      <button id="menuToggle" class="md:hidden text-white focus:outline-none cursor-pointer">
        <img src="/assets/img/menu-icon.png" alt="Menu" class="h-6 w-6">
      </button>

      <!-- Navigation Links -->
      <nav id="mainNav" class="hidden md:flex flex-col md:flex-row gap-4 text-sm md:text-base bg-emerald-950 md:bg-transparent absolute md:static top-full left-0 w-full md:w-auto px-4 py-2 md:p-0">
        <ul class="flex flex-col md:flex-row gap-4">
          <li><a href="/index.php" class="text-white hover:text-emerald-400">Sign in</a></li>
          <li><a href="/pages/feedback-form.php" class="text-white hover:text-emerald-400">Feedback</a></li>
          <li><a href="/pages/faqs.php" class="text-white hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <main class="flex-grow w-full px-4 pt-10">
    <form action="/controllers/submit-form.php" method="POST"
      class="bg-white border-2 border-emerald-800 rounded-lg p-6 md:p-8 mb-20 max-w-4xl mx-auto space-y-10 opacity-90">

      <!--  Terms Agreement Section (Preserved Class) -->
      <section class="js-terms-agreement-form space-y-4">
        <h3 class="text-lg font-bold">Terms and Privacy Agreement</h3>
        <p class="text-sm">
          By continuing, you agree to the collection and processing of your data for client satisfaction measurement...
        </p>
        <label class="flex items-center gap-2 text-sm">
          <input type="checkbox" id="agree-checkbox" class="accent-emerald-600">
          I agree to the terms and privacy policy
        </label>
        <div class="text-center">
          <button type="button" id="agree-button"
            class="bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-600 disabled:opacity-50"
            disabled>
            Continue
          </button>
        </div>
      </section>

      <section class="js-client-information-form space-y-6">
        <h4 class="text-center font-bold text-xl">109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)</h4>

        <p class="text-lg text-white font-medium bg-emerald-700 w-fit px-4 py-2 rounded">Client Information</p>

        <div class="space-y-6">
          <!-- Pangalan -->
          <div>
            <label for="name" class="block font-medium text-sm">Pangalan</label>
            <input type="text" id="name" name="name" placeholder="Name (Optional)"
              class="w-full border-2 rounded-lg p-2">
          </div>

          <!-- Petsa -->
          <div>
            <label for="date" class="block font-medium text-sm">Petsa</label>
            <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>"
              class="w-full border-2 rounded-lg p-2">
          </div>

          <!-- Edad -->
          <div>
            <label for="age" class="block font-medium text-sm">Edad</label>
            <select id="age" name="age" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected>Age</option>
              <option value="under-19">19 or lower / 19 pababa</option>
              <option value="20-34">20 - 34</option>
              <option value="35-49">35 - 49</option>
              <option value="50-64">50 - 64</option>
              <option value="65-up">65 and higher / 65 pataas</option>
            </select>
          </div>

          <!-- Kasarian -->
          <div>
            <label for="sex" class="block font-medium text-sm">Kasarian</label>
            <select id="sex" name="sex" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected>Sex</option>
              <option value="Female">Female / Babae</option>
              <option value="Male">Male / Lalaki</option>
            </select>
          </div>

          <!-- Uri ng Kliyente -->
          <div>
            <label for="customer_type" class="block font-medium text-sm">Uri ng Kliyente</label>
            <select id="customer_type" name="customer_type" onchange="updateServiceOptions()"
              class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected>Customer Type</option>
              <option value="Business">Business</option>
              <option value="Citizen">Citizen</option>
              <option value="Government">Government</option>
            </select>
            <p class="text-sm pt-2">
              <b>Notes:</b><br>
              <b>Business</b> (private school, corporations, etc.)<br>
              <b>Citizen</b> (general public, learners, parents...)<br>
              <b>Government</b> (current DepEd employees or LGU)
            </p>
          </div>

          <!-- Serbisyong Natanggap -->
          <div>
            <label for="service_availed" class="block font-medium text-sm">Serbisyong Natanggap</label>
            <select id="service_availed" name="service_availed" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected>Service Availed</option>
            </select>
          </div>

          <!-- Rehiyon -->
          <div>
            <label for="region" class="block font-medium text-sm">Rehiyon</label>
            <select id="region" name="region" class="w-full border-2 rounded-lg p-2">
              <option value="" disabled selected>Region</option>
              <?php
              $stmt = $pdo->query("SELECT id, code, name FROM regions ORDER BY code");
              while ($region = $stmt->fetch()) {
                echo "<option value=\"{$region['id']}\">{$region['code']} - {$region['name']}</option>";
              }
              ?>
            </select>
          </div>

          <!-- Error Message -->
          <p id="feedback-form-error" class="text-red-600 font-bold text-sm"></p>

          <!-- Next Button -->
          <div class="text-center">
            <button type="button" value="next"
              class="bg-emerald-800 text-white text-lg px-4 py-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600 mt-4">
              Next
            </button>
          </div>
        </div>
      </section>

      <!--Citizen Charter Awareness Form Section -->
      <section class="js-citizen-awareness-form p-5 space-y-6 max-w-4xl mx-auto">
        <h4 class="text-center font-bold text-xl">
          109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)
        </h4>

        <h3 class="text-lg font-medium text-white bg-emerald-700 w-fit px-4 py-2 rounded">
          Citizen's Charter
        </h3>

        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base">
            <i>Are you aware of the Citizen's Charter — document of services and requirements?</i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="yes_no" value="yes" class="mr-2"> Yes</label>
            <label class="block"><input type="radio" name="yes_no" value="no" class="mr-2"> No</label>
          </div>
          <p id="feedback-form-error2" class="text-red-600 font-bold text-sm"></p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 pt-4">
          <button type="button" name="previous" value="previous"
            class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600">
            Previous
          </button>
          <button type="button" name="next" value="next"
            class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600">
            Next
          </button>
        </div>
      </section>

      <!-- Citizen Charter 2 Form Section -->
      <section class="js-citizen-charter-form p-5 space-y-6 max-w-4xl mx-auto">
        <h4 class="text-center font-bold text-xl">
          109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)
        </h4>

        <h3 class="text-lg font-medium text-white bg-emerald-800 w-fit px-4 py-2 rounded">
          Citizen's Charter
        </h3>

        <!-- CC1 -->
        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base" for="cc-1">
            <i>CC1. Which of the following best describes your awareness of a Citizen’s Charter?</i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="cc-1" value="1" class="mr-2">1.) I know what a Citizen’s Charter is and I saw this office’s Citizen’s Charter.</label>
            <label class="block"><input type="radio" name="cc-1" value="2" class="mr-2">2.) I know what a Citizen’s Charter is but I did not see this office’s Citizen’s Charter.</label>
            <label class="block"><input type="radio" name="cc-1" value="3" class="mr-2">3.) I learned of the Citizen’s Charter only when I saw this office’s Citizen’s Charter.</label>
            <label class="block"><input type="radio" name="cc-1" value="4" class="mr-2">4.) I do not know what a Citizen’s Charter is and I did not see one in this office.</label>
          </div>
        </div>

        <!-- CC2 -->
        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base" for="cc-2">
            <i>CC2. If aware of Citizen’s Charter, would you say that the CC of this office was …?</i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="cc-2" value="1" class="mr-2">1.) Easy to see</label>
            <label class="block"><input type="radio" name="cc-2" value="2" class="mr-2">2.) Somewhat easy to see</label>
            <label class="block"><input type="radio" name="cc-2" value="3" class="mr-2">3.) Difficult to see</label>
            <label class="block"><input type="radio" name="cc-2" value="4" class="mr-2">4.) Not visible at all</label>
            <label class="block"><input type="radio" name="cc-2" value="5" class="mr-2">5.) N/A</label>
          </div>
        </div>

        <!-- CC3 -->
        <div class="space-y-4">
          <label class="block font-medium text-sm md:text-base" for="cc-3">
            <i>CC3. How much did the CC help you in your transaction?</i>
          </label>
          <div class="space-y-2">
            <label class="block"><input type="radio" name="cc-3" value="1" class="mr-2">1.) Helped very much</label>
            <label class="block"><input type="radio" name="cc-3" value="2" class="mr-2">2.) Somewhat helped</label>
            <label class="block"><input type="radio" name="cc-3" value="3" class="mr-2">3.) Did not help</label>
            <label class="block"><input type="radio" name="cc-3" value="4" class="mr-2">4.) N/A</label>
          </div>
        </div>

        <!-- Error Message -->
        <p id="feedback-form-error3" class="text-red-600 font-bold text-sm"></p>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-4">
          <button type="button" value="previous"
            class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600">
            Previous
          </button>
          <button type="button" value="next"
            class="bg-emerald-800 text-white p-2 rounded-lg w-full sm:w-1/2 hover:bg-emerald-600">
            Next
          </button>
        </div>
      </section>

      <!-- Client Satisfaction Form Section -->
      <section class="js-client-satisfaction-form p-5 space-y-6 max-w-4xl mx-auto">
        <h4 class="text-center font-bold text-xl">
          109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)
        </h4>

        <h3 class="bg-emerald-800 w-fit px-4 py-2 text-lg text-white font-medium rounded">
          Client Satisfaction
        </h3>

        <div class="space-y-6">
          <!-- Repeat this block for each SQD question -->
          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD1">
              <i>SQD1 - I spent an acceptable amount of time to complete my transaction (Gumugol ako ng sapat na oras upang maayos na makumpleto ang aking transaksyon) (Responsiveness) </i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD1" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD1" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD1" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD1" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD1" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD1" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD2">
              <i>SQD2 - The office accurately informed and followed the transaction's requirements and steps. (Ang opisina ay nagbigay ng tamang impormasyon at maayos na sinunod ang mga kinakailangan at proseso ng transaksyon.) (Reliability)</i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD2" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD2" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD2" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD2" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD2" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD2" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD3">
              <i>SQD3 - My transaction (including steps and payment) was simple and convenient. (Ang aking transaksyon (kasama ang mga hakbang at pagbabayad) ay simple at maginhawa.) (Access and Facilities)</i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD3" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD3" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD3" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD3" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD3" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD3" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD4">
              <i>SDQ4 - I easily found information about my transaction from the office or its website (Madali kong nahanap ang impormasyon tungkol sa aking transaksyon mula sa opisina o sa kanilang website.) (Communication)</i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD4" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD4" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD4" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD4" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD4" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD4" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD5">
              <i>SQD5 - I paid an acceptable amount of fees for my transaction. (If the service was free, mark the NOT APPLICABLE column) (Nagbayad ako ng katanggap-tanggap na halaga ng bayarin para sa aking transaksyon. (Costs)) </i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD5" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD5" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD5" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD5" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD5" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD5" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD6">
              <i>SQD6 - I am confident my transaction was secure. (Tiwala ako na ang aking transaksyon ay protektado at ligtas.) (Integrity)</i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD6" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD6" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD6" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD6" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD6" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD6" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD7">
              <i>SQD7 - The office's support was quick to respond. (Ang suporta ng opisina ay agad tumugon.) (Assurance)</i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD7" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD7" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD7" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD7" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD7" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD7" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <div class="space-y-4">
            <label class="block font-medium text-sm md:text-base" for="SQD8">
              <i>SQD8 - I got what I needed from the government office. (Nakuha ko ang kinakailangan ko mula sa tanggapan ng gobyerno.) (Outcome)</i>
            </label>
            <div class="space-y-2">
              <label class="block"><input type="radio" name="SQD8" value="5" class="mr-2"> (5) Strongly Agree</label>
              <label class="block"><input type="radio" name="SQD8" value="4" class="mr-2"> (4) Agree</label>
              <label class="block"><input type="radio" name="SQD8" value="3" class="mr-2"> (3) Neither Agree or Disagree</label>
              <label class="block"><input type="radio" name="SQD8" value="2" class="mr-2"> (2) Disagree</label>
              <label class="block"><input type="radio" name="SQD8" value="1" class="mr-2"> (1) Strongly Disagree</label>
              <label class="block"><input type="radio" name="SQD8" value="na" class="mr-2"> Not Applicable</label>
            </div>
          </div>

          <!-- Remarks -->
          <div class="space-y-2">
            <label for="remarks" class="block font-medium text-sm md:text-base">Remarks:</label>
            <textarea id="remarks" name="remarks" placeholder="Enter your answer"
              class="w-full h-40 border-2 p-2 rounded resize-none"></textarea>
          </div>

          <!-- Error Message -->
          <p id="feedback-form-error4" class="text-red-600 font-bold text-sm"></p>

          <!-- Navigation Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <button type="button" name="previous" value="previous"
              class="bg-emerald-800 p-2 w-full sm:w-1/2 text-lg text-white rounded-lg hover:bg-emerald-600">
              Previous
            </button>
            <button type="submit" name="submit" value="submit"
              class="bg-emerald-800 p-2 w-full sm:w-1/2 text-lg text-white rounded-lg hover:bg-emerald-600">
              Submit
            </button>
          </div>
        </div>
      </section>

    </form>
  </main>

  <!--Footer Section-->
  <?php include '../includes/footer.php' ?>

  <script src="/assets/js/feedbackFormNavigation.js"></script>
  <script src="/assets/js/serviceAvailedOptions.js"></script>
</body>

</html>