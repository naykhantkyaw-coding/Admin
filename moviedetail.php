<?php
include('includes/config.php');

if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Tbl_Movies WHERE MovieId = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$movie) {
        header('Location: movies.php');
        exit();
    }
    
    // Fetch ticket fees for this movie
    $ticket_stmt = $pdo->prepare("SELECT * FROM Tbl_TicketFees WHERE MovieId = ? ORDER BY Price");
    $ticket_stmt->execute([$movie_id]);
    $ticket_fees = $ticket_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fetch already booked seats for this movie to prevent double booking
    $booked_stmt = $pdo->prepare("SELECT SeatNo FROM Tbl_Booking WHERE MovieId = ? AND Status != 'Cancelled'");
    $booked_stmt->execute([$movie_id]);
    $booked_seats = $booked_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    // Convert booked seats to array of individual seats
    $occupied_seats = [];
    foreach ($booked_seats as $seat_group) {
        $seats = explode(',', $seat_group);
        $occupied_seats = array_merge($occupied_seats, $seats);
    }
    
} else {
    header('Location: movies.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($movie['MovieTitle']) ?> - Movie Details</title>
  <link rel="stylesheet" href="assets/css/home.css">
  <link rel="stylesheet" href="assets/css/moviedetail.css">
  <!-- Bootstrap CSS for better navbar styling -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="home.php">
            <i class="fas fa-film me-2"></i>MovieHub
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">
                        <i class="fas fa-home"></i>Home
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <!-- User Info (if logged in) -->
                <?php if(isset($_SESSION['user_id'])): ?>
                <div class="user-info">
                    <i class="fas fa-user me-1"></i>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                </div>
                <?php endif; ?>
                
                <!-- Check Booking Button -->
                <a href="check-booking.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-ticket-alt me-1"></i>Check Booking
                </a>
                
                <!-- Logout Button -->
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php" class="btn btn-custom" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
                <?php else: ?>
                <a href="login.php" class="btn btn-custom">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="wrap">
    <a href="home.php" class="back-button">← Back to Movies</a>
    
    <div class="movie-detail">
        <div class="movie-header">
            <div class="movie-poster">
                <?php
                $movie_image = !empty($movie['ImageUrl']) ? $movie['ImageUrl'] : 'assets/img/movie/default-movie.jpg';
                ?>
                <img src="<?= $movie_image ?>" alt="<?= htmlspecialchars($movie['MovieTitle']) ?>">
            </div>
            <div class="movie-info">
                <h1><?= htmlspecialchars($movie['MovieTitle']) ?></h1>
                <div class="movie-meta">
                    <p><strong>Genre:</strong> <?= htmlspecialchars($movie['Genrer']) ?></p>
                    <p><strong>Duration:</strong> <?= htmlspecialchars($movie['Duration']) ?> minutes</p>
                    <p><strong>Release Date:</strong> <?= date('M d, Y', strtotime($movie['ReleaseDate'])) ?></p>
                    <p><strong>Status:</strong> <?= htmlspecialchars($movie['Status']) ?></p>
                </div>
                <div class="movie-description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($movie['Description'])) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Process -->
    <div class="booking-process">
        <div class="booking-steps">
            <div class="booking-step active" data-step="1">1. View Seat Layout</div>
            <div class="booking-step" data-step="2">2. Select Seats & Ticket Class</div>
            <div class="booking-step" data-step="3">3. Confirm Booking</div>
        </div>
        
        <!-- Step 1: Seat Layout Display -->
        <div class="step-content active" id="step1">
            <h3 class="text-center mb-4">Theater Seat Layout</h3>
            
            <div class="seat-layout">
                <div class="screen">🎬 S C R E E N 🎬</div>
                
                <div class="seat-legend">
                    <div class="legend-item">
                        <div class="legend-color seat-standard"></div>
                        <span>Standard Seats (Rows S)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color seat-premium"></div>
                        <span>Premium Seats (Rows P)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color seat-vip"></div>
                        <span>VIP Seats (Rows V)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color seat-occupied"></div>
                        <span>Occupied Seats</span>
                    </div>
                </div>
                
                <div class="seats-display" id="seatsDisplay">
                    <!-- Seat layout will be generated by JavaScript -->
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    This shows the current seat availability. You'll select your preferred seats in the next step.
                </div>
            </div>
            
            <!-- In Step 1 -->
<div class="booking-navigation">
    <button class="btn btn-secondary" onclick="window.history.back()">Cancel</button>
    <button class="btn btn-primary" onclick="nextStep(2)">Next: Select Seats</button>
</div>
        </div>
        
        <!-- Step 2: Seat Selection & Ticket Class -->
        <div class="step-content" id="step2">
            <h3 class="text-center mb-4">Select Your Seats</h3>
            
            <div class="seat-selection">
                <!-- Standard Seats -->
                <div class="seat-category standard">
                    <h5><i class="fas fa-chair text-secondary"></i> Standard Seats</h5>
                    <div class="seat-dropdowns" id="standardSeats">
                        <!-- Standard seat dropdowns will be generated by JavaScript -->
                    </div>
                </div>
                
                <!-- Premium Seats -->
                <div class="seat-category premium">
                    <h5><i class="fas fa-couch text-info"></i> Premium Seats</h5>
                    <div class="seat-dropdowns" id="premiumSeats">
                        <!-- Premium seat dropdowns will be generated by JavaScript -->
                    </div>
                </div>
                
                <!-- VIP Seats -->
                <div class="seat-category vip">
                    <h5><i class="fas fa-crown text-warning"></i> VIP Seats</h5>
                    <div class="seat-dropdowns" id="vipSeats">
                        <!-- VIP seat dropdowns will be generated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Ticket Class Selection -->
            <h4 class="mt-5 mb-3">Ticket Standards</h4>
            <div class="ticket-classes">
                <?php foreach($ticket_fees as $ticket): ?>
                <div class="ticket-class" data-class="<?= htmlspecialchars($ticket['TicketClass']) ?>" data-price="<?= $ticket['Price'] ?>">
                    <h4><?= htmlspecialchars($ticket['TicketClass']) ?></h4>
                    <div class="ticket-price">$<?= number_format($ticket['Price'], 2) ?></div>
                    <ul class="ticket-features">
                        <?php if($ticket['TicketClass'] == 'VIP'): ?>
                        <li>Premium seating</li>
                        <li>Extra legroom</li>
                        <li>Complimentary snacks</li>
                        <?php elseif($ticket['TicketClass'] == 'Premium'): ?>
                        <li>Comfortable seating</li>
                        <li>Good viewing angle</li>
                        <li>Standard amenities</li>
                        <?php else: ?>
                        <li>Standard seating</li>
                        <li>Regular viewing</li>
                        <li>Basic amenities</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="selected-info mt-4">
                <p><strong>Selected Seats:</strong> <span id="step2SelectedSeats">None</span></p>
                <!-- <p><strong>Selected Class:</strong> <span id="selectedClass">None</span></p>
                <p><strong>Price per Ticket:</strong> $<span id="pricePerTicket">0.00</span></p> -->
                <p><strong>Number of Tickets:</strong> <span id="step2Quantity">0</span></p>
                <p><strong>Total Price:</strong> $<span id="step2TotalPrice">0.00</span></p>
            </div>
            
            <div class="booking-navigation">
                <button class="btn btn-secondary" onclick="prevStep(1)">Back to Layout</button>
                <button class="btn btn-primary" id="nextToStep3" onclick="nextStep(3)" disabled>Next: Confirm Booking</button>
            </div>
        </div>
        
        <!-- Step 3: Booking Confirmation -->
        <div class="step-content" id="step3">
            <h3 class="text-center mb-4">Confirm Your Booking</h3>
            
            <div class="booking-summary">
                <h5>Booking Summary</h5>
                <div class="summary-item">
                    <span>Movie:</span>
                    <span><?= htmlspecialchars($movie['MovieTitle']) ?></span>
                </div>
                <div class="summary-item">
                    <span>Selected Seats:</span>
                    <span id="summarySeats"></span>
                </div>
                <!-- <div class="summary-item">
                    <span>Ticket Class:</span>
                    <span id="summaryClass"></span>
                </div>
                <div class="summary-item">
                    <span>Price per Ticket:</span>
                    <span id="summaryPricePerTicket"></span>
                </div> -->
                <div class="summary-item">
                    <span>Number of Tickets:</span>
                    <span id="summaryQuantity"></span>
                </div>
                <div class="summary-total">
                    <span>Total Amount:</span>
                    <span id="summaryTotalPrice"></span>
                </div>
            </div>
            
            <?php if(isset($_SESSION['user_id'])): ?>
            <form method="POST" action="processbooking.php">
                <input type="hidden" name="movie_id" value="<?= $movie_id ?>">
                <input type="hidden" name="selected_seats" id="formSeats">
                <input type="hidden" name="ticket_class" id="formClass">
                <input type="hidden" name="quantity" id="formQuantity">
                <input type="hidden" name="total_price" id="formTotalPrice">
                
                <div class="text-center">
                    <button type="submit" class="btn btn-success btn-booking">
                        <i class="fas fa-check me-2"></i>Confirm Booking
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-warning text-center">
                <p>Please <a href="login.php" class="alert-link">login</a> to complete your booking.</p>
            </div>
            <?php endif; ?>
            
            <div class="booking-navigation">
                <button class="btn btn-secondary" onclick="prevStep(2)">Back to Seat Selection</button>
                <button class="btn btn-outline-secondary" onclick="window.history.back()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Booking process variables
let selectedSeats = [];
let occupiedSeats = <?= json_encode($occupied_seats) ?>;

// Seat type to ticket class and price mapping
const seatPriceMapping = {};
<?php foreach($ticket_fees as $ticket): ?>
seatPriceMapping['<?= $ticket['TicketClass'] ?>'] = <?= $ticket['Price'] ?>;
<?php endforeach; ?>

// Initialize seat layout display
function initializeSeatLayout() {
    const display = document.getElementById('seatsDisplay');
    display.innerHTML = ''; // Clear existing content
    
    const rows = [
        { type: 'standard', label: 'S', count: 10 },
        { type: 'standard', label: 'S', count: 10 },
        { type: 'premium', label: 'P', count: 8 },
        { type: 'premium', label: 'P', count: 8 },
        { type: 'vip', label: 'V', count: 6 },
        { type: 'vip', label: 'V', count: 6 }
    ];
    
    rows.forEach(rowConfig => {
        const rowElement = document.createElement('div');
        rowElement.className = 'seat-row';
        
        const rowLabel = document.createElement('div');
        rowLabel.className = 'row-label';
        rowLabel.textContent = rowConfig.label;
        rowElement.appendChild(rowLabel);
        
        for (let i = 1; i <= rowConfig.count; i++) {
            const seat = document.createElement('div');
            const seatNumber = rowConfig.label + i.toString().padStart(2, '0');
            
            seat.className = `seat-display seat-${rowConfig.type}`;
            seat.textContent = i;
            
            // Mark as occupied if already booked
            if (occupiedSeats.includes(seatNumber)) {
                seat.classList.add('seat-occupied');
            }
            
            rowElement.appendChild(seat);
        }
        
        display.appendChild(rowElement);
    });
}

// Initialize seat selection dropdowns
function initializeSeatSelection() {
    // Clear existing dropdowns first
    document.getElementById('standardSeats').innerHTML = '';
    document.getElementById('premiumSeats').innerHTML = '';
    document.getElementById('vipSeats').innerHTML = '';
    
    const seatCategories = [
        { type: 'standard', container: 'standardSeats', rows: ['S1', 'S2'], seatsPerRow: 10 },
        { type: 'premium', container: 'premiumSeats', rows: ['P1', 'P2'], seatsPerRow: 8 },
        { type: 'vip', container: 'vipSeats', rows: ['V1', 'V2'], seatsPerRow: 6 }
    ];
    
    seatCategories.forEach(category => {
        const container = document.getElementById(category.container);
        
        category.rows.forEach(row => {
            const formGroup = document.createElement('div');
            formGroup.className = 'form-group';
            
            const label = document.createElement('label');
            label.textContent = `Row ${row}`;
            label.htmlFor = `seats-${row}`;
            formGroup.appendChild(label);
            
            const select = document.createElement('select');
            select.className = 'form-control seat-select';
            select.id = `seats-${row}`;
            select.dataset.row = row;
            select.dataset.type = category.type;
            
            // Add default option
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = `Select ${row} seat`;
            select.appendChild(defaultOption);
            
            // Add seat options
            for (let i = 1; i <= category.seatsPerRow; i++) {
                const seatNumber = row + i.toString().padStart(2, '0');
                const option = document.createElement('option');
                option.value = seatNumber;
                option.textContent = `Seat ${i}`;
                
                // Disable if already occupied
                if (occupiedSeats.includes(seatNumber)) {
                    option.disabled = true;
                    option.textContent += ' (Occupied)';
                }
                
                select.appendChild(option);
            }
            
            select.addEventListener('change', function() {
                updateSeatSelection();
            });
            
            formGroup.appendChild(select);
            container.appendChild(formGroup);
        });
    });
    
    // Initialize seat selection
    updateSeatSelection();
}

// Calculate total price based on individual seat prices
function calculateTotalPrice() {
    if (selectedSeats.length === 0) {
        return 0;
    }
    
    let totalPrice = 0;
    const seatBreakdown = {
        'Standard': { count: 0, price: seatPriceMapping['Standard'] || 0 },
        'Premium': { count: 0, price: seatPriceMapping['Premium'] || 0 },
        'VIP': { count: 0, price: seatPriceMapping['VIP'] || 0 }
    };
    
    // Calculate price for each seat based on its type
    selectedSeats.forEach(seat => {
        const seatType = seat.charAt(0); // Get first character (S, P, or V)
        let ticketClass = '';
        
        switch(seatType) {
            case 'S':
                ticketClass = 'Standard';
                break;
            case 'P':
                ticketClass = 'Premium';
                break;
            case 'V':
                ticketClass = 'VIP';
                break;
        }
        
        if (ticketClass && seatBreakdown[ticketClass]) {
            seatBreakdown[ticketClass].count++;
            totalPrice += seatBreakdown[ticketClass].price;
        }
    });
    
    // Update seat breakdown display
    updateSeatBreakdownDisplay(seatBreakdown);
    
    return totalPrice;
}

// Update seat breakdown display
function updateSeatBreakdownDisplay(breakdown) {
    let breakdownHTML = '';
    let hasSeats = false;
    
    for (const [ticketClass, data] of Object.entries(breakdown)) {
        if (data.count > 0) {
            hasSeats = true;
            const classTotal = data.count * data.price;
            breakdownHTML += `
                <div class="breakdown-item">
                    <span>${ticketClass} (${data.count} × $${data.price.toFixed(2)})</span>
                    <span>$${classTotal.toFixed(2)}</span>
                </div>
            `;
        }
    }
    
    const breakdownContainer = document.getElementById('seatBreakdown');
    if (breakdownContainer) {
        if (hasSeats) {
            breakdownContainer.innerHTML = breakdownHTML;
            breakdownContainer.style.display = 'block';
        } else {
            breakdownContainer.style.display = 'none';
        }
    }
}

// Update seat selection
function updateSeatSelection() {
    selectedSeats = [];
    const selects = document.querySelectorAll('.seat-select');
    
    selects.forEach(select => {
        if (select.value && select.value !== '') {
            selectedSeats.push(select.value);
        }
    });
    
    console.log('Selected seats:', selectedSeats);
    
    document.getElementById('step2SelectedSeats').textContent = selectedSeats.join(', ') || 'None';
    document.getElementById('step2Quantity').textContent = selectedSeats.length;
    
    // Calculate total price based on individual seat prices
    const totalPrice = calculateTotalPrice();
    document.getElementById('step2TotalPrice').textContent = totalPrice.toFixed(2);
    
    // Update price calculation
    updatePriceCalculation(totalPrice);
}

// Update price calculation
function updatePriceCalculation(totalPrice) {
    const quantity = selectedSeats.length;
    
    // Enable/disable next button
    const nextButton = document.getElementById('nextToStep3');
    if (quantity > 0) {
        nextButton.disabled = false;
        console.log('Next button enabled');
    } else {
        nextButton.disabled = true;
        console.log('Next button disabled - Quantity:', quantity);
    }
    
    // Update form fields for booking
    updateFormFields(totalPrice);
}

// Update form hidden fields
function updateFormFields(totalPrice) {
    document.getElementById('formSeats').value = selectedSeats.join(',');
    document.getElementById('formQuantity').value = selectedSeats.length;
    document.getElementById('formTotalPrice').value = totalPrice.toFixed(2);
    
    // For mixed seat types, we need to determine what to put in ticket_class
    // We'll use "Mixed" or the dominant class, but the actual pricing is per seat
    let dominantClass = '';
    const classCount = {
        'Standard': 0,
        'Premium': 0,
        'VIP': 0
    };
    
    selectedSeats.forEach(seat => {
        const seatType = seat.charAt(0);
        switch(seatType) {
            case 'S': classCount['Standard']++; break;
            case 'P': classCount['Premium']++; break;
            case 'V': classCount['VIP']++; break;
        }
    });
    
    // Find dominant class
    let maxCount = 0;
    for (const [ticketClass, count] of Object.entries(classCount)) {
        if (count > maxCount) {
            maxCount = count;
            dominantClass = ticketClass;
        }
    }
    
    // If mixed classes, use "Mixed" otherwise use the dominant class
    const uniqueClasses = Object.values(classCount).filter(count => count > 0).length;
    const ticketClassValue = uniqueClasses > 1 ? 'Mixed' : dominantClass;
    document.getElementById('formClass').value = ticketClassValue;
}

// Initialize ticket class display (read-only information)
function initializeTicketClasses() {
    const ticketClasses = document.querySelectorAll('.ticket-class');
    
    // Remove click events to prevent manual selection
    ticketClasses.forEach(ticket => {
        ticket.style.cursor = 'default';
        ticket.style.pointerEvents = 'none';
        ticket.style.transition = 'none';
    });
    
    // Add price information display
    const ticketSection = document.querySelector('.ticket-classes');
    const priceInfo = document.createElement('div');
    priceInfo.className = 'col-12';
    priceInfo.innerHTML = `
        <div class="alert alert-info mt-3">
            <h6><i class="fas fa-info-circle me-2"></i>Pricing Information</h6>
            <div class="row mt-2">
                <div class="col-md-4">
                    <strong>Standard Seats:</strong> $${(seatPriceMapping['Standard'] || 0).toFixed(2)}
                </div>
                <div class="col-md-4">
                    <strong>Premium Seats:</strong> $${(seatPriceMapping['Premium'] || 0).toFixed(2)}
                </div>
                <div class="col-md-4">
                    <strong>VIP Seats:</strong> $${(seatPriceMapping['VIP'] || 0).toFixed(2)}
                </div>
            </div>
            <div class="mt-2">
                <small>Total price is calculated based on individual seat prices.</small>
            </div>
        </div>
    `;
    ticketSection.parentNode.insertBefore(priceInfo, ticketSection.nextSibling);
    
    // Add seat breakdown container
    const breakdownContainer = document.createElement('div');
    breakdownContainer.id = 'seatBreakdown';
    breakdownContainer.className = 'seat-breakdown mt-3';
    breakdownContainer.style.display = 'none';
    priceInfo.parentNode.insertBefore(breakdownContainer, priceInfo.nextSibling);
}

// Step navigation
function nextStep(step) {
    console.log('Moving to step:', step, 'Selected seats:', selectedSeats.length);
    
    // Validate current step
    if (step === 2) {
        // No validation needed when moving to step 2 from step 1
        console.log('Moving to step 2 - no seat validation needed');
    }
    
    if (step === 3) {
        if (selectedSeats.length === 0) {
            alert('Please select at least one seat.');
            return;
        }
    }
    
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all steps
    document.querySelectorAll('.booking-step').forEach(stepEl => {
        stepEl.classList.remove('active');
    });
    
    // Mark previous steps as completed and activate current
    for (let i = 1; i <= step; i++) {
        const stepElement = document.querySelector(`.booking-step[data-step="${i}"]`);
        if (i < step) {
            stepElement.classList.add('completed');
        } else if (i === step) {
            stepElement.classList.add('active');
        }
    }
    
    // Show current step content
    document.getElementById(`step${step}`).classList.add('active');
    
    // Initialize components when moving to step
    if (step === 1) {
        initializeSeatLayout();
    } else if (step === 2) {
        initializeSeatSelection();
        initializeTicketClasses();
    } else if (step === 3) {
        updateBookingSummary();
    }
}

function prevStep(step) {
    nextStep(step);
}

// Update booking summary for step 3
function updateBookingSummary() {
    const totalPrice = calculateTotalPrice();
    const quantity = selectedSeats.length;
    
    document.getElementById('summarySeats').textContent = selectedSeats.join(', ');
    document.getElementById('summaryQuantity').textContent = quantity;
    document.getElementById('summaryTotalPrice').textContent = '$' + totalPrice.toFixed(2);
    
    // Show price breakdown in summary
    const seatBreakdown = {
        'Standard': { count: 0, price: seatPriceMapping['Standard'] || 0 },
        'Premium': { count: 0, price: seatPriceMapping['Premium'] || 0 },
        'VIP': { count: 0, price: seatPriceMapping['VIP'] || 0 }
    };
    
    selectedSeats.forEach(seat => {
        const seatType = seat.charAt(0);
        switch(seatType) {
            case 'S': seatBreakdown['Standard'].count++; break;
            case 'P': seatBreakdown['Premium'].count++; break;
            case 'V': seatBreakdown['VIP'].count++; break;
        }
    });
    
    let breakdownHTML = '';
    for (const [ticketClass, data] of Object.entries(seatBreakdown)) {
        if (data.count > 0) {
            const classTotal = data.count * data.price;
            breakdownHTML += `
                <div class="summary-item">
                    <span>${ticketClass} Seats (${data.count} × $${data.price.toFixed(2)})</span>
                    <span>$${classTotal.toFixed(2)}</span>
                </div>
            `;
        }
    }
    
    // Insert breakdown before total
    const summaryContainer = document.querySelector('.booking-summary');
    const totalElement = document.querySelector('.summary-total');
    const existingBreakdown = document.getElementById('summaryBreakdown');
    
    if (existingBreakdown) {
        existingBreakdown.remove();
    }
    
    const breakdownElement = document.createElement('div');
    breakdownElement.id = 'summaryBreakdown';
    breakdownElement.innerHTML = breakdownHTML;
    summaryContainer.insertBefore(breakdownElement, totalElement);
    
    // Update form hidden fields
    updateFormFields(totalPrice);
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeSeatLayout();
    console.log('Page loaded successfully');
});
</script>
</body>
</html>