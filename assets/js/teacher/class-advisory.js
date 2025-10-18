export function initClassAdvisory() {
  fetch('/controllers/teacher/get-students.php')
    .then(res => res.json())
    .then(students => {
      const container = document.getElementById('advisoryContainer');
      if (!container) return;

      if (students.length === 0) {
        container.innerHTML = `
          <div class="p-6 flex flex-col items-center justify-center text-gray-500 text-sm space-y-2">
            <img src="/assets/img/empty-student.png" alt="No students" class="h-16 w-16 opacity-50">
            <span>No students found in your advisory class.</span>
          </div>
        `;
        return;
      }

      const table = document.createElement('table');
      table.className = 'min-w-full table-auto border-collapse';

      table.innerHTML = `
        <thead class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
          <tr>
            <th class="px-4 py-3">Student Name</th>
            <th class="px-4 py-3">Attendance</th>
            <th class="px-4 py-3">Grades</th>
            <th class="px-4 py-3">Feedback</th>
          </tr>
        </thead>
        <tbody class="text-sm text-gray-800">
          ${students.map(s => `
            <tr class="border-t hover:bg-gray-50">
              <td class="px-4 py-2">${s.first_name} ${s.last_name}</td>
              <td class="px-4 py-2">
                <button data-student-id="${s.id}" class="mark-attendance-btn bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">Mark</button>
              </td>
              <td class="px-4 py-2">
                <button class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Enter</button>
              </td>
              <td class="px-4 py-2">
                <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">Add</button>
              </td>
            </tr>
          `).join('')}
        </tbody>
      `;

      container.innerHTML = '';
      container.appendChild(table);

      // ✅ Attach modal triggers after rendering
      document.querySelectorAll('.mark-attendance-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.studentId;
          openAttendanceModal(id);
        });
      });
    })
    .catch(err => {
      const container = document.getElementById('advisoryContainer');
      if (container) {
        container.innerHTML = '<div class="p-4 text-red-500 text-sm">Failed to load students. Please try again later.</div>';
      }
      console.error('Error fetching students:', err);
    });
}