// Filter companies based on search input
function filterCompanies() {
    const input = document.getElementById('search').value.toLowerCase();
    const companies = document.getElementsByClassName('company-card');

    for (let i = 0; i < companies.length; i++) {
        const name = companies[i].textContent.toLowerCase();
        companies[i].style.display = name.includes(input) ? '' : 'none';
    }
}

function hideOtherCompanies(companyId) {
    const companies = document.getElementsByClassName('company-card');
    let selectedCompany = document.querySelector('.company-card.selected');

    // If the clicked company is already selected, reset all companies
    if (selectedCompany && selectedCompany.getAttribute('data-id') === String(companyId)) {
        for (let i = 0; i < companies.length; i++) {
            companies[i].style.display = ''; // Show all companies again
            companies[i].classList.remove('selected');
        }
    } else {
        for (let i = 0; i < companies.length; i++) {
            if (companies[i].getAttribute('data-id') === String(companyId)) {
                companies[i].classList.add('selected');
                companies[i].style.display = ''; // Keep selected company visible
            } else {
                companies[i].classList.remove('selected');
                companies[i].style.display = 'none'; // Hide others
            }
        }
    }


    // Update URL and show images
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;
    window.location.href = `?company_id=${companyId}&month=${month}&year=${year}`;
}

// Filter images based on date
function filterImages() {
    const companyId = new URLSearchParams(window.location.search).get('company_id');
    if (companyId) {
        hideOtherCompanies(companyId); // Reapply hiding logic
    }
}

// Maximize image in modal
function maximizeImage(imgElement) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = 'flex';
    modalImg.src = imgElement.getAttribute('data-src');
}

// Close modal
function closeModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
}

// Download image as PDF
function downloadAsPDF() {
    const { jsPDF } = window.jspdf;
    const img = document.getElementById('modalImage');
    const pdf = new jsPDF();

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    canvas.width = img.naturalWidth;
    canvas.height = img.naturalHeight;
    ctx.drawImage(img, 0, 0);

    const imgData = canvas.toDataURL('image/jpeg');
    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight);
    pdf.save('company_image.pdf');
}