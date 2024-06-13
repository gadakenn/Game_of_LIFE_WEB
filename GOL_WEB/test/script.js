document.getElementById('hintButton').addEventListener('click', function() {
    var hint = document.getElementById('hint');
    if (hint.classList.contains('hidden')) {
        hint.classList.remove('hidden');
    } else {
        hint.classList.add('hidden');
    }
});
