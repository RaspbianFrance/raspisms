#!/usr/bin/env python3
# -*- coding: UTF-8 -*-
# vim: expandtab sw=4 ts=4 sts=4:
#

from __future__ import print_function
import gammu
import sys
import json

def main():
    state_machine = gammu.StateMachine()
    
    if len(sys.argv) < 2:
        sys.exit(1)
    else :
        state_machine.ReadConfig(Filename=sys.argv[1])
        del sys.argv[1]

    state_machine.Init()

    status = state_machine.GetSMSStatus()

    remain = status['SIMUsed'] + status['PhoneUsed'] + status['TemplatesUsed']

    start = True

    try:
        while remain > 0:
            if start:
                sms = state_machine.GetNextSMS(Start=True, Folder=0)
                start = False
            else:
                sms = state_machine.GetNextSMS(
                    Location=sms[0]['Location'], Folder=0
                )
            remain = remain - len(sms)

            for m in sms :
                if m['State'] != 'UnRead' :
                    continue

                print(json.dumps({
                    'number': m['Number'],
                    'at': str(m['DateTime']),
                    'status': m['State'],
                    'text': m['Text'],
                }))

    except gammu.ERR_EMPTY:
        #do noting
        return True


if __name__ == '__main__':
    main()
