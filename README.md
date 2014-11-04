IWusers module
============
IWusers module 3.1.0 **for Zikula 1.3.x** from [Intraweb project](https://github.com/intraweb-modules13)

  - The module IWusers **extends users functions**: extra profile fields, import/export functions, avatar management...

Install notes
-------------
  - This modules needs IWmain module (https://github.com/intraweb-modules13/IWmain) for general config and API for IW modules
  - You must copy vendor folder to zikula root path.

Import user notes
-----------------
  - **Import user functions directly from a csv file**.
    - Csv fields:
uname,new_uname,email,password,forcechgpass,activated,firstname,lastname1,lastname2,birthdate,gender,code,in,out
    - **uname** is the only mandatory field. Order does not matter.
    - **email, firstname, lastname1, lastname2, birthdate, gender and code** fields override old content (blank fields in the file clear content).
    - **new_uname** will change the username. Warning with duplicates! The action is sequential (register to register)
    - **password** field. Empty field will not override password.
    - **forcechgpass** field: 1-> creates the var, 0-> remove this (if exists). Empty or other values do nothing.
    - **activated** field. For new users: 0->off (activated=0), other value-> activated (=1). Updating users: 1->activated (=1), 0->off (activated=0), empty or other values->nothing (it keeps old data)
    - **in** and **out** fields. Adding (in) and removing (out) groups assignment. The content is a sequence of **id** separated by **|** character.
