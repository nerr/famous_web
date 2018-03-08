#!/usr/bin/env python3
# -*- coding: utf-8 -*-
import csv
import pymysql

# load csv format from an exported csv file

# with open('taobao/taobao1.csv', 'r', encoding='utf16') as csvfile:
#     reader = csv.reader(csvfile, delimiter=' ', quotechar='|')
#
#     for row in reader:
#         print(row)
#         print('----')



# load goods info from database
connection = pymysql.connect("192.168.88.10", "root", "911geforce", "lily", charset='utf8mb4')
cursor = connection.cursor()
cursor.execute("SET NAMES utf8")
cursor.execute("SELECT * FROM `view_goods` where id>31 order by goods_desc, id")
data = cursor.fetchall()
connection.close()



# insert goods info to csv
for inf in data:
    #print(inf)
    print("%03d-正品Lily折扣特卖超低价格%s%s[%s]%d" % (inf[1], inf[4], inf[8], inf[2], inf[5]))
