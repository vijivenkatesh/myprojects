#!/usr/bin/env python

import nmap
import ipaddress

def validate_subnet(subnet):
    try:
        network = ipaddress.IPv4Network(subnet, strict=False)
        return network
    except ValueError:
        print("Invalid subnet format.")
        return None
		
def scan_network(subnet):
		
	#validate subnet address	
    network = validate_subnet(subnet)
    if network is None:
        return []
		
	#scan subnet
    nm = nmap.PortScanner()
    a=nm.scan(hosts=subnet, arguments='-sP') 
    active_hosts = []
    print('    ')
    print('Scanning....')
    print('                ')
    print('IPV4                   mac')
	# loop ip address
    for k,v in a['scan'].items(): 
	    if str(v['status']['state']) == 'up':
			#print(str(v))
		    try:    print (str(v['addresses']['ipv4']) + '    ' + str(v['addresses']['mac']))
		    except: print (str(v['addresses']['ipv4']))
    print('     ')
    print(' Scanning Completed')

# Main function to run the scanner
if __name__ == "__main__":
	#get user input
	subnet = input("Enter a subnet (e.g., 192.168.1.0/24):")
	scan_network(subnet)
