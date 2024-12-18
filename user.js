let editedData = {}; // Object to store edited data for all rows

// Enable editing for a specific cell on double-click
document.querySelectorAll('.editable').forEach(cell => {
   cell.addEventListener('dblclick', function () {
       if (!cell.querySelector('input')) {
           const originalValue = cell.innerText;
           const column = cell.dataset.column;
           const userName = cell.closest('tr').id.split('_')[1];

           // Replace the cell content with an input field
           cell.innerHTML = `<input type="text" value="${originalValue}">`;
           const input = cell.querySelector('input');
           input.focus();

           input.addEventListener('blur', function () {
               const newValue = input.value;

               // Restore cell to text and save changes to editedData object
               cell.innerHTML = newValue;

               if (!editedData[userName]) {
                   editedData[userName] = {};
               }
               editedData[userName][column] = newValue;
           });

           input.addEventListener('keydown', function (e) {
               if (e.key === 'Enter') {
                   input.blur(); // Save changes on Enter key
               }
           });
       }
   });
});

document.getElementById('editButton').addEventListener('click', function () {
   if (Object.keys(editedData).length > 0) {
       const xhr = new XMLHttpRequest();
       xhr.open('POST', 'userinfo.php', true);
       xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
       xhr.onload = function () {
           if (xhr.status === 200) {
               showPopup(xhr.responseText, 'success');
               setTimeout(() => location.reload(), 3000); // Reload page to show updated data
           } else {
               showPopup('Error saving changes', 'error');
           }
       };

       // Send edited data as JSON to the server
       xhr.send(`editData=${JSON.stringify(editedData)}`);
   } else {
       showPopup('No changes to save', 'info');
   }
});

// Show popup messages
function showPopup(message, type) {
   const popup = document.getElementById('popup');
   popup.innerText = message;
   popup.className = `popup ${type}`; // Apply success, error, or info class
   popup.style.display = 'block';

   setTimeout(() => {
       popup.style.display = 'none';
   }, 3000);
}

