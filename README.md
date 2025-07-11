<h5>fname,lname,email,phone</h5>

CREATE TABLE stud_basic_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
        fname VARCHAR(100) NOT NULL,
        lname VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        phone VARCHAR(15) NOT NULL 
);

INSERT INTO `stud_basic_info`( `fname`, `lname`, `email`, `phone`) VALUES ('...','...','....','....')

<h5>gender ,address1 ,address2 ,city  ,state ,country ,zip   ,resume </h5>

CREATE TABLE stud_gen_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
        gender ENUM('male', 'female', 'other') NOT NULL,
        address1 VARCHAR(255),
        address2 VARCHAR(255),
        city VARCHAR(100),
        state VARCHAR(100),
        country VARCHAR(100),
        zip VARCHAR(10),
        photo VARCHAR(255),
    FOREIGN KEY (student_id) REFERENCES stud_basic_info(id) ON DELETE CASCADE ON UPDATE CASCADE
);


INSERT INTO `stud_gen_info`(`student_id`, `gender`, `address1`, `address2`, `city`, `state`, `country`, `zip`, `photo`) VALUES ('..','...','....','....','...','..','...','...','..')

<h5>qualification ,percentage ,passing year ,university </h5>

CREATE TABLE qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qualification_name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE stud_academic_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
        qualification_id INT NOT NULL,
        percentage DECIMAL(5,2) NOT NULL,
        passing_year YEAR NOT NULL,
        university VARCHAR(150) NOT NULL,
    FOREIGN KEY (student_id) REFERENCES stud_basic_info(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (qualification_id) REFERENCES qualifications(id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `stud_academic_info`( `student_id`, `qualification_id`, `percentage`, `passing_year`, `university`) VALUES ('..','...','...','..','..')

<h5>Hobbies </h5>

CREATE TABLE hobbies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hobby_name VARCHAR(100) NOT NULL UNIQUE
);



CREATE TABLE stud_hobbies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    hobby_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES stud_basic_info(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (hobby_id) REFERENCES hobbies(id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `stud_hobbies`( `student_id`, `hobby_id`) VALUES ('..','..')
