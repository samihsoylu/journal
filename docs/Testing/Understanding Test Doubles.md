# Software Testing: Understanding Test Doubles

In software testing, **test doubles** are essential tools for simplifying and controlling the testing environment. They effectively mimic the behavior of real components in a controlled setting. This document outlines four primary types of test doubles used in this project: **Dummy**, **Fake**, **Spy**, and **Stub**, each playing a unique role in enhancing testing efficacy.

## Dummy
### Definition

* **Dummies** are the simplest form of test doubles. They are used as placeholders to satisfy the parameter lists of methods but are never actually used in a meaningful way.

### Usage

* Typically used when a method argument is required but not used.
* Useful in setting up test environments where the actual object is not of interest.

### Characteristics

* Has no actual implementation.
* Not intended for use in the test logic.
* Helps in fulfilling interface or argument requirements.

## Fake
### Definition

* **Fakes** are working implementations, but they take shortcuts and have simplified versions of production code. They can be more complex than stubs or dummies and are used in scenarios where using real objects is impractical or cumbersome.

### Usage

* Commonly used to mimic database interactions, file systems, network services, etc.
* Suitable for integration tests where interactions with external services are required but not the focus of the test.

### Characteristics

* Has a functional implementation, but simplified.
* Not suitable for production but can be used for testing.
* Helps simulate scenarios that involve complex operations or external dependencies.

## Spy
### Definition

* **Spies** are test doubles that record information about how they were used, such as tracking method calls and arguments.

### Usage

* Used to verify that certain methods were called, or certain interactions with the mock object occurred.
* Ideal for testing side effects and indirect outputs of methods.

### Characteristics

* Can return predefined responses like a stub.
* Records interactions for later verification.
* Useful for more detailed behavior testing.

## Stub
### Definition

* **Stubs** provide predetermined responses to calls. They are used to replace real components that the system-under-test interacts with.

### Usage

* Employed when the outcome of a test depends on the response from the stubbed component.
* Commonly used to simulate responses from methods that interact with external services or complex logic.

### Characteristics

* Returns controlled responses to specific calls.
* Does not keep track of its usage.
* Ideal for isolating the system-under-test from external dependencies.