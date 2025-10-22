import { initStudentDeleteModal } from '../modal.js';
export function refreshStudentTable(gradeLevelId = '') {
  const tbody = document.getElementById('studentTableBody');
  if (!tbody) return;

  const url = gradeLevelId
    ? `/ajax/get-students.php?grade_level_id=${gradeLevelId}`
    : '/ajax/get-students.php';

  fetch(url)
    .then(res => res.json())
    .then(data => {
      if (!data.success || !Array.isArray(data.students) || data.students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-gray-500">No students found.</td></tr>';
        return;
      }

      tbody.innerHTML = '';

      data.students.forEach(student => {
        const row = document.createElement('tr');
        row.classList.add('border-t', 'hover:bg-emerald-100', 'transition-all', 'duration-300');

        row.innerHTML = `
          <!-- Student Info -->
          <td class="px-4 py-2 whitespace-nowrap">
  <div class="flex items-center gap-3 min-w-0">
    <img src="${student.photo_path || '/assets/img/default-avatar.png'}"
         class="w-10 h-10 rounded-full object-cover border border-gray-300 flex-shrink-0"
         alt="Photo">
    <div class="min-w-0">
      <span class="block font-medium truncate text-sm sm:text-base">${student.full_name}</span>
    </div>
  </div>
</td>

          <!-- LRN -->
          <td class="px-4 py-2">${student.lrn}</td>

          <!-- Gender -->
          <td class="px-4 py-2 capitalize">${student.gender}</td>

          <td class="px-4 py-2">${student.grade_label} - ${student.section_label}</td>

          <!-- Actions -->
<td class="px-4 py-2 whitespace-nowrap align-middle">
  <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 justify-start sm:justify-center">
    
    <!-- View Icon -->
    <div class="relative">
      <a href="/pages/admin/view-student.php?id=${student.id}"
         class="peer inline-flex items-center justify-center w-9 h-9 rounded-full bg-white hover:bg-pink-100 hover:scale-110 transition duration-200 ">
        <img src="/assets/img/details.png" alt="View Details" class="w-5 h-5" />
      </a>
      <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
        View
      </div>
    </div>

    <!-- Edit Icon -->
    <div class="relative">
      <button type="button"
              class="peer edit-student inline-flex items-center justify-center w-9 h-9 rounded-full bg-white hover:bg-blue-100 hover:scale-110 transition duration-200 cursor-pointer"
              data-id="${student.id}">
        <img src="/assets/img/edit-icon.png" alt="Edit" class="w-4 h-4" />
      </button>
      <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
        Edit
      </div>
    </div>

    <!-- Delete Icon -->
    <div class="relative">
      <button type="button"
              class="peer delete-student inline-flex items-center justify-center w-9 h-9 rounded-full bg-white hover:bg-red-100 hover:scale-110 transition duration-200 cursor-pointer"
              data-id="${student.id}" data-name="${student.full_name}">
        <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
      </button>
      <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
        Delete
      </div>
    </div>

  </div>
</td>
        `;

        tbody.appendChild(row);
      });

      initStudentEditHandler?.();
      initStudentDeleteModal?.();
    })
    .catch(() => {
      tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-red-500">Failed to load students.</td></tr>';
    });
}


export function initStudentTable() {
  refreshStudentTable();
}

export function initStudentEditHandler() {
  document.querySelectorAll('.edit-student').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-id');
      if (id) {
        window.location.href = `/pages/admin/edit-student.php?id=${id}`;
      }
    });
  });
}