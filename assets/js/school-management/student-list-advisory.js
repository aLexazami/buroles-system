import { toggleModal } from '../modal.js';

export function refreshStudentList(classId, role = 'admin') {
  const tbody = document.getElementById('studentListTableBody');
  if (!tbody || !classId) return;

  fetch(`/ajax/get-students-by-class.php?class_id=${classId}`)
    .then(res => res.json())
    .then(data => {
      if (!data.success || !Array.isArray(data.students) || data.students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-gray-500">No students assigned to this advisory class.</td></tr>';
        return;
      }

      tbody.innerHTML = '';

      data.students.forEach(student => {
        const row = document.createElement('tr');
        row.classList.add('border-t', 'hover:bg-emerald-100', 'transition-all', 'duration-300');

        const viewLink = role === 'staff'
          ? `/pages/staff/view-student.php?id=${student.id}&class_id=${classId}`
          : `/pages/admin/view-student.php?id=${student.id}&class_id=${classId}`;

        let actions = `
          <div class="relative">
            <a href="${viewLink}"
               class="peer inline-flex items-center justify-center w-9 h-9 rounded-full bg-white hover:bg-pink-100 hover:scale-110 transition duration-200">
              <img src="/assets/img/details.png" alt="View Details" class="w-5 h-5" />
            </a>
            <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
              View
            </div>
          </div>
        `;

        if (role === 'admin') {
          actions += `
            <div class="relative">
              <button type="button"
                      class="peer delete-student inline-flex items-center justify-center w-9 h-9 rounded-full bg-white hover:bg-red-100 hover:scale-110 transition duration-200 cursor-pointer"
                      data-id="${student.id}"
                      data-name="${student.full_name}">
                <img src="/assets/img/delete-icon.png" alt="Delete" class="w-4 h-4" />
              </button>
              <div class="absolute bottom-full mb-1 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-700 text-white text-xs rounded opacity-0 peer-hover:opacity-100 transition duration-200 pointer-events-none z-10">
                Delete
              </div>
            </div>
          `;
        }

        row.innerHTML = `
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
          <td class="px-4 py-2">${student.lrn}</td>
          <td class="px-4 py-2 capitalize">${student.gender}</td>
          <td class="px-4 py-2">${student.grade_label} - ${student.section_label}</td>
          <td class="px-4 py-2 whitespace-nowrap align-middle">
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 justify-start sm:justify-center">
              ${actions}
            </div>
          </td>
        `;

        tbody.appendChild(row);

        if (role === 'admin') {
          const deleteBtn = row.querySelector('.delete-student');
          if (deleteBtn) {
            deleteBtn.addEventListener('click', () => {
              const form = document.getElementById('deleteStudentClassForm');
              const nameSpan = document.getElementById('deleteStudentName');
              const studentId = deleteBtn.dataset.id;
              const studentName = deleteBtn.dataset.name;

              const oldInput = document.getElementById('deleteStudentClassStudentId');
              if (oldInput) oldInput.remove();

              const newInput = document.createElement('input');
              newInput.type = 'hidden';
              newInput.name = 'student_id';
              newInput.id = 'deleteStudentClassStudentId';
              newInput.value = studentId;
              form.prepend(newInput);

              nameSpan.textContent = studentName;
              toggleModal('deleteStudentClassModal', true);
            });
          }
        }
      });
    })
    .catch(() => {
      tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-red-500">Failed to load students.</td></tr>';
    });
}

export function initStudentList(role = 'admin') {
  const classId = document.getElementById('classId')?.value;
  if (classId) refreshStudentList(classId, role);
}