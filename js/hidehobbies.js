document.getElementById('stud_form').addEventListener('submit', function (e) {
    const select = document.getElementById('hobby_select');
    const hiddenInput = document.getElementById('hobbies_final');
    let selected = Array.from(select.selectedOptions).map(opt => opt.value);

    if (selected.includes('OTHERS')) {
        const other = prompt('Enter your hobby:');
        if (other && other.trim() !== '') {
            selected[selected.indexOf('OTHERS')] = other.trim();
        } else {
            selected = selected.filter(v => v !== 'OTHERS');
        }
    }

    hiddenInput.value = selected.join(',');
});