<?php
require_once __DIR__ . '/../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="/src/styles.css" rel="stylesheet">
  <title>BES Feedback Form</title>
</head>
<body class="bg-gradient-to-b from-white to-emerald-800 min-h-screen flex flex-col ">
  <!-- Header Section -->
  <header class=" bg-emerald-950 shadow-md sticky-top-0 z-10 p-1">
    <section class="max-w-4xl mx-auto flex justify-between items-center">
      <div class="flex items-center ">
        <img src="../assets/img/bes-logo1.png" alt="Burol Elementary School Logo" class="h-20 border rounded-full bg-white">
        <p class="text-3xl font-medium text-white ml-5">
          Burol Elementary School
        </p>
      </div>
      <nav>
        <ul class="flex space-x-4 mr-3">
          <li><a href="../index.php" class="text-white text-md hover:text-emerald-400 pr-10">Sign in</a></li>
          <li><a href="/pages/feedback-form.php" class="text-white text-md hover:text-emerald-400">Feedback</a></li>
          <li><a href="/pages/faqs.php" class="text-white text-md hover:text-emerald-400">FAQs</a></li>
        </ul>
      </nav>
    </section>
  </header>

  <!-- Main Content Section -->
  <!-- Feedback Form Section -->
  <main class="max-w-4xl mx-auto px-4 pt-10">

    <form action="/controllers/submit-form.php" method="POST" class="bg-white opacity-75 border-2 border-emerald-800 rounded-lg p-8 mb-19">
      <!-- Terms Agreement Form -->
      <section class="js-terms-agreement-form">
        <h3 class="text-lg font-bold mb-2">Terms and Privacy Agreement</h3>
        <p class="mb-4 text-sm">
          By continuing, you agree to the collection and processing of your data for client satisfaction measurement. Your responses will remain confidential and used only for service improvement.
        </p>
        <label class="flex items-center space-x-2">
          <input type="checkbox" id="agree-checkbox" class="accent-emerald-600">
          <span class="text-sm">I agree to the terms and privacy policy</span>
        </label>
        <div class="mt-4 text-center">
          <button type="button" id="agree-button" class="bg-emerald-700 text-white px-4 py-2 rounded hover:bg-emerald-600 disabled:opacity-50" disabled>
            Continue
          </button>
        </div>
      </section>

      <section class="js-client-information-form">
        <h4 class="text-center font-bold text-xl">109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)</h4>
        <br>
        <br>
        <p class="text-lg text-white font-medium bg-emerald-700 w-fit p-2">Client Information</p>
        <br>
        <div class="flex flex-col">
          <label class="font-medium pb-2"><i>Pangalan</i></label>
          <input type="text" id="name" name="name" placeholder="Name (Optional)" class="border-2 rounded-lg p-2">
          <br>
          <label class="font-medium pb-2"><i>Petsa</i></label>
          <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" placeholder="Date" class="border-2 rounded-lg p-2">
          <br>
          <label class="font-medium pb-2"><i>Edad</i></label>
          <select id="age" name="age" class="border-2 rounded-lg p-2">
            <option value="" disabled selected>Age</option>
            <option value="under-19">19 or lower / 19 pababa</option>
            <option value="20-34">20 - 34</option>
            <option value="35-49">35 - 49</option>
            <option value="50-64">50 - 64</option>
            <option value="65-up">65 and higher / 65 pataas</option>
          </select>
          <br>
          <label class="font-medium pb-2"><i>Kasarian</i></label>
          <select id="sex" name="sex" class="border-2 rounded-lg p-2">
            <option value="" disabled selected>Sex</option>
            <option value="Female">Female / Babae</option>
            <option value="Male">Male / Lalaki</option>
          </select>
          <br>
          <label class="font-medium pb-2"><i>Uri ng Kliyente</i></label>
          <select id="customer_type" name="customer_type" onchange="updateServiceOptions()" class="border-2 rounded-lg p-2">
            <option value="" disabled selected>Customer Type</option>
            <option value="Business">Business</option>
            <option value="Citizen">Citizen</option>
            <option value="Government">Government</option>
          </select>
          <p class="pt-2"><b>Notes:</b> <br>
            <b>Business</b> (private school, corporations, etc.)<br>
            <b>Citizen</b> (general public, learners, parents, former DepEd employees, researchers, NGOs etc)<br>
            <b>Government</b> (current DepEd employees or employees of other government agencies & LGU)
          </p>
          </p>
          <br>
          <label class="font-medium pb-2"><i>Serbisyong Natanggap</i></label>
          <select id="service_availed" name="service_availed" class="border-2 rounded-lg p-2">
            <option value="" disabled selected>Service Availed</option>
          </select>
          <br>
          <label class="font-medium pb-2"><i>Rehiyon</i></label>
          <select id="region" name="region" class="border-2 rounded-lg p-2">
            <option value="" disabled selected>Region</option>
            <?php
            $stmt = $pdo->query("SELECT id, code, name FROM regions ORDER BY code");
            while ($region = $stmt->fetch()) {
              echo "<option value=\"{$region['id']}\">{$region['code']} - {$region['name']}</option>";
            }
            ?>
          </select>
          <br>
          <br>
          <!-- Error message container -->
          <p id="feedback-form-error" style="color: red; font-weight: bold;"></p>
          <div class="text-center">
            <button type="button" value="next" class="bg-emerald-800  p-2 w-1/2  text-lg rounded-lg text-white hover:bg-emerald-600 mt-5">Next</button>
          </div>
          <br>
          <br>
        </div>
      </section>

      <!--Citizen Charter Awareness Form Section -->
      <section class="js-citizen-awareness-form p-5 ">
        <h4 class="text-center font-bold text-xl">109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)</h4>
        <br>
        <br>
        <h3 class="text-lg text-white font-medium bg-emerald-700 w-fit p-2">Citizen's Charter</h3>
        <br>
        <label class="cc-question"><i>Are you aware of the Citizen's Charter - document of services and requirements?</i></label>
        <br>
        <br>
        <input type="radio" name="yes_no" value="yes" class="mr-2">
        <label for="yes" class="">Yes</label><br>
        <input type="radio" name="yes_no" value="no" class="mr-2">
        <label for="no">No</label>
        <br>
        <br>

        <!-- Error message container -->
        <p id="feedback-form-error2" style="color: red; font-weight: bold;"></p>
        <br>
        <br>
        <div class="flex flex-row">
          <button type="button" name="previous" value="previous" class="bg-emerald-800  p-2 w-1/2  text-lg rounded-lg text-white mr-2 hover:bg-emerald-600">Previous</button>
          <button type="button" name="next" value="next" class="bg-emerald-800  p-2 w-1/2  text-lg rounded-lg text-white hover:bg-emerald-600">Next</button>
        </div>
      </section>

      <!-- Citizen Charter 2 Form Section -->
      <section class="js-citizen-charter-form p-5 ">
        <h4 class="text-center font-bold text-xl">109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)</h4>
        <br>
        <br>
        <h3 class="font-medium text-white bg-emerald-800 w-fit p-2 text-lg">Citizen's Charter</h3>
        <br>
        <label class="font-medium" for="cc-1"><i>CC1. Which of the following best describes your awareness of a Citizen’s Charter?</i></label>
        <br>
        <br>
        <input type="radio" name="cc-1" value="1" class="mr-2">
        <label for="1">1. I know what a Citizen’s Charter is and I saw this office’s Citizen’s Charter.</label>
        <br>
        <input type="radio" name="cc-1" value="2" class="mr-2">
        <label for="2">2. I know what a Citizen’s Charter is but I did not see this office’s Citizen’s Charter.</label>
        <br>
        <input type="radio" name="cc-1" value="3" class="mr-2">
        <label for="3">3. I learned of the Citizen’s Charter only when I saw this office’s Citizen’s Charter.</label>
        <br>
        <input type="radio" name="cc-1" value="4" class="mr-2">
        <label for="4">4. I do not know what a Citizen’s Charter is and I did not see one in this office. (Answer ‘N/A’ on CC2 and CC3)</label>
        <br>
        <br>
        <label class="font-medium" for="cc-2"><i>CC2. If aware of Citizen’s Charter (answered 1-3 in CC1), would you say that the CC of this office was …?</i></label>
        <br>
        <br>
        <input type="radio" name="cc-2" value="1" class="mr-2">
        <label for="1">1. Easy to see.</label>
        <br>
        <input type="radio" name="cc-2" value="2" class="mr-2">
        <label for="2">2. Somewhat easy to see.</label>
        <br>
        <input type="radio" name="cc-2" value="3" class="mr-2">
        <label for="3">3. Difficult to see.</label>
        <br>
        <input type="radio" name="cc-2" value="4" class="mr-2">
        <label for="4">4. Not visible at all</label>
        <br>
        <input type="radio" name="cc-2" value="5" class="mr-2">
        <label for="5">5. N/A</label>
        <br>
        <br>
        <label class="font-medium" for="cc-3"><i>CC3. If aware of Citizen’s Charter (answered 1-3 in CC1), how much did the CC help you in your transaction?</i></label>
        <br>
        <br>
        <input type="radio" name="cc-3" value="1" class="mr-2">
        <label for="1">1. Helped very much .</label>
        <br>
        <input type="radio" name="cc-3" value="2" class="mr-2">
        <label for="2">2. Somewhat helped.</label>
        <br>
        <input type="radio" name="cc-3" value="3" class="mr-2">
        <label for="3">3. Did not help.</label>
        <br>
        <input type="radio" name="cc-3" value="4" class="mr-2">
        <label for="4">4. N/A</label>
        <br>
        <br>
        <!-- Error message container -->
        <p id="feedback-form-error3" style="color: red; font-weight: bold;"></p>
        <br>
        <br>
        <div class="flex flex-row">
          <button type="button" value="previous" class="bg-emerald-800 w-1/2 text-white p-2 text-lg rounded-lg mr-2 hover:bg-emerald-600">Previous</button>
          <button type="button" value="next" class="bg-emerald-800 w-1/2 text-lg text-white p-2 rounded-lg hover:bg-emerald-600">Next</button>
        </div>
      </section>

      <!-- Client Satisfaction Form Section -->
      <section class="js-client-satisfaction-form p-5">
        <h4 class="text-center font-bold text-xl">109843 BUROL ELEMENTARY SCHOOL Client Satisfaction Measurement (CSM) (2025)</h4>
        <br>
        <br>
        <h3 class="bg-emerald-800 w-fit p-2 text-lg text-white font-medium">Client Satisfaction</h3>
        <br>
        <br>
        <label class="font-medium" for="SQD1"><i>SQD1 - I spent an acceptable amount of time to complete my transaction (Gumugol ako ng sapat na oras upang maayos na makumpleto ang aking transaksyon) (Responsiveness) </i></label>
        <br>
        <br>
        <input type="radio" name="SQD1" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD1" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD1" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD1" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD1" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD1" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <label class="font-medium" for="SQD2"><i>SQD2 - The office accurately informed and followed the transaction's requirements and steps. (Ang opisina ay nagbigay ng tamang impormasyon at maayos na sinunod ang mga kinakailangan at proseso ng transaksyon.) (Reliability)</i></label>
        <br>
        <br>
        <input type="radio" name="SQD2" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD2" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD2" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD2" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD2" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD2" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <label class="font-medium" for="SQD3"><i>SQD3 - My transaction (including steps and payment) was simple and convenient. (Ang aking transaksyon (kasama ang mga hakbang at pagbabayad) ay simple at maginhawa.) (Access and Facilities)</i></label>
        <br>
        <br>
        <input type="radio" name="SQD3" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD3" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD3" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD3" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD3" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD3" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <label class="font-medium" for="SQD4"><i>SDQ4 - I easily found information about my transaction from the office or its website (Madali kong nahanap ang impormasyon tungkol sa aking transaksyon mula sa opisina o sa kanilang website.) (Communication)</i></label>
        <br>
        <br>
        <input type="radio" name="SQD4" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD4" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD4" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD4" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD4" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD4" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <label class="font-medium" for="SQD5"><i>SQD5 - I paid an acceptable amount of fees for my transaction. (If the service was free, mark the NOT APPLICABLE column) (Nagbayad ako ng katanggap-tanggap na halaga ng bayarin para sa aking transaksyon. (Costs)) </i></label>
        <br>
        <br>
        <input type="radio" name="SQD5" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD5" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD5" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD5" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD5" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD5" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <label class="font-medium" for="SQD6"><i>SQD6 - I am confident my transaction was secure. (Tiwala ako na ang aking transaksyon ay protektado at ligtas.) (Integrity)</i></label>
        <br>
        <br>
        <input type="radio" name="SQD6" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD6" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD6" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD6" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD6" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD6" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <label class="font-medium" for="SQD7"><i>SQD7 - The office's support was quick to respond. (Ang suporta ng opisina ay agad tumugon.) (Assurance)
          </i></label>
        <br>
        <br>
        <input type="radio" name="SQD7" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD7" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD7" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD7" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD7" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD7" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <label class="font-medium" for="SQD8"><i>SQD8 - I got what I needed from the government office. (Nakuha ko ang kinakailangan ko mula sa tanggapan ng gobyerno.) (Outcome)</i></label>
        <br>
        <br>
        <input type="radio" name="SQD8" value="5">
        <label for="5">( 5 ) Strongly Agree</label>
        <br>
        <input type="radio" name="SQD8" value="4">
        <label for="4">( 4 ) Agree</label>
        <br>
        <input type="radio" name="SQD8" value="3">
        <label for="3">( 3 ) Neither Agree or Disagree</label>
        <br>
        <input type="radio" name="SQD8" value="2">
        <label for="2">( 2 ) Disagree</label>
        <br>
        <input type="radio" name="SQD8" value="1">
        <label for="1">( 1 ) Strongly Disagree</label>
        <br>
        <input type="radio" name="SQD8" value="na">
        <label for="na">Not Applicable</label>
        <br>
        <br>
        <br>
        <label for="remarks" class="font-medium">Remarks:</label>
        <br>
        <br>
        <textarea id="remarks" name="remarks" placeholder="Enter your answer" class="w-full h-50 border-2 p-2 resize-none"></textarea>
        <br>
        <br>
        <!-- Error message container -->
        <p id="feedback-form-error4" style="color: red; font-weight: bold;"></p>
        <br>
        <br>
        <div class="flex flex-row">
          <button type="button" name="previous " value="previous" class="bg-emerald-800 p-2 w-1/2 text-lg text-white rounded-lg mr-2 hover:bg-emerald-600">Previous</button>
          <button type="submit" name="submit" value="submit" class="bg-emerald-800 p-2 w-1/2 text-lg text-white rounded-lg hover:bg-emerald-600">Submit</button>
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