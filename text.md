<!--! fname  -->
<!--! lname  -->
<!--! email  -->
<!--! phone  -->

CREATE TABLE stud_basic_info (
    id INT AUTO_INCREMENT PRIMARY KEY,
        fname VARCHAR(100) NOT NULL,
        lname VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        phone VARCHAR(15) NOT NULL 
);

INSERT INTO `stud_basic_info`( `fname`, `lname`, `email`, `phone`) VALUES ('parmar','pradip','parmarparth@gmail.com','7103425012')

<!--! gender -->
<!--! address1 -->
<!--! address2 -->
<!--! city  -->
<!--! state -->
<!--! country -->
<!--! zip   -->
<!--! resume -->

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


INSERT INTO `stud_gen_info`(`student_id`, `gender`, `address1`, `address2`, `city`, `state`, `country`, `zip`, `photo`) VALUES ('54','male','Junagadh','Junagadh','Junagadh','Gujrat','India','235689','')
<!--! qualification   -->
<!--! percentage -->
<!--! passing year -->
<!--! university -->

CREATE TABLE qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    qualification_name VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO `qualifications`( `qualification_name`)
 VALUES
  ('BCA'),
  ('MCA'),
  ('B.COM'),
  ('M.COM'),
  ('B.TECH'),
  ('M.TECH');



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

INSERT INTO `stud_academic_info`( `student_id`, `qualification_id`, `percentage`, `passing_year`, `university`) VALUES ('54','3','67.36','2023',Mmarvadi University')
<!--! Hobbies -->

CREATE TABLE hobbies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hobby_name VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO `hobbies`( `hobby_name`)
 VALUES
  ('Gaming'),
  ('Sports'),
  ('Travel'),
  ('Reading'),
  ('Photography'),
  ('Cooking/Baking');

CREATE TABLE stud_hobbies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    hobby_id INT NOT NULL,
    FOREIGN KEY (student_id) REFERENCES stud_basic_info(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (hobby_id) REFERENCES hobbies(id) ON DELETE CASCADE ON UPDATE CASCADE
);

INSERT INTO `stud_hobbies`( `student_id`, `hobby_id`) VALUES ('54','3')


SELECT 
    sb.id AS student_id,
    sb.fname,
    sb.lname,
    sb.email,
    sb.phone,
    sg.gender,
    sg.address1,
    sg.address2,
    sg.city,
    sg.state,
    sg.country,
    sg.zip,
    sa.percentage,
    sa.passing_year,
    sa.university,
    q.qualification_name,
    GROUP_CONCAT(h.hobby_name SEPARATOR ', ') AS hobbies
FROM stud_basic_info sb
LEFT JOIN stud_gen_info sg ON sb.id = sg.student_id
LEFT JOIN stud_academic_info sa ON sb.id = sa.student_id
LEFT JOIN qualifications q ON sa.qualification_id = q.id
LEFT JOIN stud_hobbies sh ON sb.id = sh.student_id
LEFT JOIN hobbies h ON sh.hobby_id = h.id
GROUP BY sb.id;



-> qualification (DYNAMIC) -> (BCA , MCA , B.COM , MCOM, B.TECH ,M.TECH, B.A , M.A , OTHERS )
IF USER CAN CLICK OTHERS THTA HE CAN SHOW INPUT FILED THAT ADD NEW qualification FOR THE USER AND THAT WAS DYNAMIC ADDITON

-> Hobbies  (Watching Movies/TV,Gaming,Writing/Blogging ,Sports ,Travel,Reading,Volunteering/Community Involvement, Learning Languages ,Photography, Playing a Musical Instrument, Graphic Design/Digital Art ,Cooking/Baking , Woodworking/DIY Projects , Coding/Programming , OTHERS ) => IF USER CAN CLICK OTHERS THTA HE CAN SHOW INPUT FILED THAT ADD NEW qualification FOR THE USER AND THAT WAS DYNAMIC ADDITON 


    <!--* fname  -->
    <!--* lname  -->
    <!--* email  -->
    <!--* phone  -->
    <!--* resume(photo upload) -->
    <!--* gender -->
    <!--* address1 -->
    <!--* address2 -->
    <!--* city  -->
    <!--* state -->
    <!--* country -->
    <!--* zip   -->
    <!--* qualification (DYNAMIC) -->
    <!--* percentage -->
    <!--* passing year -->
    <!--* university -->
    <!--* Hobbies (DYNAMIC) -->




INSERT INTO `stud_basic_info`( `fname`, `lname`, `email`, `phone`) VALUES ('parmar','pradip','parmarparth@gmail.com','7103425012')
INSERT INTO `stud_gen_info`(`student_id`, `gender`, `address1`, `address2`, `city`, `state`, `country`, `zip`, `photo`) VALUES ('54','male','Junagadh','Junagadh','Junagadh','Gujrat','India','235689','')
INSERT INTO `stud_academic_info`( `student_id`, `qualification_id`, `percentage`, `passing_year`, `university`) VALUES ('54','3','67.36','2023',Mmarvadi University')
INSERT INTO `stud_hobbies`( `student_id`, `hobby_id`) VALUES ('54','3')




DELETE FROM stud_basic_info;
ALTER TABLE stud_basic_info AUTO_INCREMENT = 1;
