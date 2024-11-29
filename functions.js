function updateHiddenFields() {
    // Get the select form for clicking a specific patient.
    var select = document.getElementById('patient-name');
    var selectedOption = select.options[select.selectedIndex];

    // Get the data attributes passed along with each patient option.
    var firstName = selectedOption.getAttribute('data-firstname');
    var lastName = selectedOption.getAttribute('data-lastname');
    var medRound = selectedOption.getAttribute('data-medround');
    var medDate = selectedOption.getAttribute('data-meddate');

    // Update the value of hidden inputs to store the data attributes above.
    // This will make them accessible as their own data "elements" via a POST request.
    document.getElementById('patientFirstName').value = firstName;
    document.getElementById('patientLastName').value = lastName;
    document.getElementById('medRound').value = medRound;
    document.getElementById('medDate').value = medDate;

    //document.getElementById('submit-patient').value = "Patients loaded!"

    // The user should only be able to go to the dispensing page if they have
    //  actually selected a patient. So, if the selected index is 0, the user is
    //  still on the default "Select a patient" option, and shouldn't be able to click.
    // If NO patients matched the selected criteria, no patients will show in the dropdown,
    //  and the user can never select anything other than "Select a patient", so they will
    //  never move to the Dispense page.
    if (select.selectedIndex !== 0) {
        document.getElementById('submit-patient').value = "Generate Timetable";
        document.getElementById('submit-patient').disabled = false;
    }
}


function hideButton() {
    // Hide the email notification button after sending email.
    document.getElementById('mail-sent').style.display = 'none';
}

function checkSelection() {

    // Check names.
    var validRegex = /^(([A-Za-z]+[\-\'\s]?)*([A-Za-z]+)?)$/;
    // acceptable characters: a-z, A-Z, ' - space.
    // ^(([A-Za-z]+[\-\'\s]?)
    // + = there must be AT LEAST ONE instance of a character before the possible appearance of hyphens and apostrophes.
    // ? = these characters COULD appear in the string, but they don't HAVE to! but if they DO appear, it should only be once
    //     since it is not possible to have a name like "Jean--Luc" --> the hyphen should only be appearing once!
    // * = the previous characters followed by zero or more of a combination of ([A-Za-z]+)?) characters where
    //     those letters could be followed by a SPACE (since a person might have two first names, like "Mary Jane Mustaine").
    // $/ = no trailing space. 

    var firstNameField = document.getElementById('patient-firstName').value.trim();
    var lastNameField = document.getElementById('patient-lastName').value.trim();
    var pracNameField = document.getElementById('prac-name').value.trim();

    if (!(firstNameField.match(validRegex)) || !(lastNameField.match(validRegex)) || !(pracNameField.match(validRegex))) {
        alert("The name fields should only contain letters, hyphens, or whitespace.");
        return false;
    }

    // Check date. You shouldn't select a specific date setting without selecting a date with the date picker!
    if (!document.getElementById('any').checked) {
        if (document.getElementById('round-date').value === "") {
            alert("If you do not want to display all dates, you must select a date first.");
            return false; // prevent submission.
        }
    }
    return true;
}