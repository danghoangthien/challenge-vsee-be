# Visitor Flow Test Cases

## 1. Authentication Tests

### TC-1.1: Visitor Login
**Objective**: Verify visitor can successfully login
- **Precondition**: Valid visitor account exists
- **Steps**:
  1. Navigate to login page
  2. Enter valid credentials
  3. Submit login form
- **Expected Result**: 
  - Successfully logged in
  - Redirected to visitor dashboard
  - WebSocket connection established

### TC-1.2: Non-Visitor Login Attempt
**Objective**: Verify non-visitor users cannot access visitor features
- **Precondition**: Valid non-visitor account exists
- **Steps**:
  1. Login with non-visitor account
  2. Attempt to access visitor features
- **Expected Result**: 
  - Access denied
  - Unauthorized message displayed
  - Redirected to appropriate page

## 2. Notification System Tests

### TC-2.1: WebSocket Connection
**Objective**: Verify notification channel subscription
- **Precondition**: Visitor is logged in
- **Steps**:
  1. Check WebSocket connection status
  2. Verify channel subscription
- **Expected Result**:
  - Successfully connected to WebSocket
  - Subscribed to correct notification channel (`visitor.{id}`)

### TC-2.2: Provider Message Reception
**Objective**: Verify all types of provider messages are received
- **Precondition**: Visitor is logged in and in queue
- **Test Cases**:
  a. Pickup Notice
  b. Postpone Notice
  c. Status Updates
  d. Queue Position Updates
  e. Examination Information
- **Expected Result for each**:
  - Message received in real-time
  - Correct message content displayed
  - Proper formatting in display area

### TC-2.3: Message Display
**Objective**: Verify messages are properly displayed on visitor page
- **Precondition**: Messages received via WebSocket
- **Steps**:
  1. Check message appears in display area
  2. Verify message formatting
  3. Test multiple messages handling
- **Expected Result**:
  - Messages displayed in correct order
  - Proper formatting maintained
  - All message types correctly rendered

## 3. Lounge Queue Tests

### TC-3.1: Queue Entry
**Objective**: Verify visitor can join queue
- **Precondition**: Visitor is logged in
- **Steps**:
  1. Request to join queue
  2. Check queue position
- **Expected Result**:
  - Successfully added to queue
  - Assigned correct queue position
  - Receive confirmation message

### TC-3.2: Queue Exit
**Objective**: Verify visitor can exit queue
- **Precondition**: Visitor is in queue
- **Steps**:
  1. Request to exit queue
  2. Confirm exit
- **Expected Result**:
  - Successfully removed from queue
  - Receive exit confirmation
  - Other queue positions updated

### TC-3.3: Provider Pickup Response
**Objective**: Verify proper handling of provider pickup
- **Precondition**: Visitor is in queue
- **Steps**:
  1. Provider selects visitor
  2. Check notification received
  3. Verify status change
- **Expected Result**:
  - Pickup notification received
  - Status changed to "with provider"
  - Removed from queue

## 4. Edge Cases and Error Handling

### TC-4.1: Connection Loss
**Objective**: Verify system behavior during connection issues
- **Precondition**: Visitor is active in system
- **Steps**:
  1. Simulate connection loss
  2. Restore connection
- **Expected Result**:
  - Connection loss detected
  - Reconnection attempted
  - Session/state properly restored

### TC-4.2: Multiple Device Login
**Objective**: Verify handling of multiple login attempts
- **Precondition**: Visitor account exists
- **Steps**:
  1. Login on first device
  2. Attempt login on second device
- **Expected Result**:
  - Appropriate handling of multiple sessions
  - Clear user notification
  - Session management policy enforced

### TC-4.3: Queue State Recovery
**Objective**: Verify queue state preservation
- **Precondition**: Visitor is in queue
- **Steps**:
  1. Simulate page refresh/reload
  2. Check queue state
- **Expected Result**:
  - Queue position maintained
  - State correctly restored
  - No duplicate queue entries

## 5. Integration Tests

### TC-5.1: End-to-End Flow
**Objective**: Verify complete visitor journey
- **Precondition**: System is operational
- **Steps**:
  1. Login
  2. Join queue
  3. Receive updates
  4. Get picked up
  5. Complete examination
  6. Exit system
- **Expected Result**:
  - All steps complete successfully
  - Proper state transitions
  - All notifications received

### TC-5.2: Provider-Visitor Interaction
**Objective**: Verify provider-visitor communication
- **Precondition**: Both provider and visitor are active
- **Steps**:
  1. Provider sends various notifications
  2. Check visitor reception
  3. Verify interaction states
- **Expected Result**:
  - All communications successful
  - Proper state synchronization
  - Correct handling of all message types

## Test Environment Requirements

1. **Browser Compatibility**:
   - Chrome (latest)
   - Firefox (latest)
   - Safari (latest)
   - Edge (latest)

2. **Device Types**:
   - Desktop
   - Tablet
   - Mobile

3. **Network Conditions**:
   - Stable connection
   - Slow connection
   - Intermittent connection

## Test Data Requirements

1. **User Accounts**:
   - Valid visitor accounts
   - Non-visitor accounts
   - Inactive accounts

2. **Queue Scenarios**:
   - Empty queue
   - Single visitor
   - Multiple visitors
   - Maximum capacity

3. **Message Types**:
   - All provider message variations
   - System notifications
   - Error messages 