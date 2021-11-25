# TCC module for Moodle 

The TCC module allows the exchange of work between groups of students and teachers, with status of corrections, mandatory forms,  chat and notes for the posts.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
Without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  


## Contributions

Originally written by Café EAD

Developed for Faculdade Educacional da Lapa - FAEL


## Installation
1- Download the zip file into this repository

2- In Moodle logged in as an administrator click on Site Administration > Plugins > Install Plugins and upload the zip file.

3- Follow Plugin Installation Steps.

## Creating the activity in Moodle

1- Within a course in Moodle add the TCC activity.

2- Name the activity, description and date it will be available.

3- Choose the level of education, the plugin is divided into two options:

Choose the level of education, the plugin is divided into two options:

Graduação / Estágio: average of work grades and grade of the in-person presentation.
Pós-graduação: sum of the work grades and the grade of the in-person presentation.

4- Configure the types of notes. If you have a face-to-face presentation, you will have a grade just for her, so mark Examing as "SIM". If you have a note per post, mark the selection and each post adds a note to the note for the in-person presentation.

5- Choose a maximum grade for the activity and another for the face-to-face evaluation, if any.

6 - As an additional step, we can choose a TCC starter form containing some fields that the student fills in before they start submitting posts. To choose which field to make available, just check it.

7 - Another additional non-mandatory feature is importing posts from another activity. If you have a TCC activity or Task activity in another course that depends on this one, you can bring the import choosing which activity you want.

8 - The last step is to configure the posts. The system already has 4 posts added, where it is possible to define an opening and closing date for each post and how many files the student can send. You can remove posts or add more.

9 - Click confirm.

10 - A screen will appear with all the settings to be confirmed.


## Creating groups

The TCC Activity depends on groups in the Moodle course, that is, each group generates a post level. It can be groups with 1 or more students and 1 teacher to make corrections.

1- Create 1 group and add a teacher and a student.

## TCC - Status

The TCC activity has been split to work with specific statuses depending on each situation.

Pendente - when a form is available and the student has not yet submitted any response.

Aprovação - when a form is available and the student has answered the items but the teacher has not corrected it yet.

Aguardando postagem - when all forms have already been approved or do not include the form and it is waiting for the student to send a post.

Enviado -  when the student sends a file at some stage and the teacher needs correction.

Em Correção - when the teacher downloads the file sent by the student but still does not return a correction.

Orientado - when the teacher sends the corrected file to the student.

Aguardando banca -if the student has a face-to-face evaluation, when giving a grade for the work, the status will wait for the grade from the board.

Finalizado - when the teacher applies a face-to-face evaluation grade, or if he doesn't have this evaluation, when he assigns the work grade, the activity ends.

## Chat

O professor poderá se comunicar com os alunos de cada grupo por meio do ícone de chat no canto de cada caixa. Também terá mensagens pré-formatadas que podem responder rapidamente aos alunos.


## Upgrading

Before upgrading it is advisable that you test the upgrade first on a COPY of your production site, to make sure it works as you expect.

### Backup important data ###
There are three areas that should be backed up before any upgrade:

* Moodle dataroot (For example, server/moodledata)
* Moodle database (For example, your Postgres or MySQL database dump)


#### TCCcafeead 3.8 ####
You can only upgrade from Moodle 3.1 or later.
