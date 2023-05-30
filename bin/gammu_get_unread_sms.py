#!/usr/bin/env python3
# -*- coding: UTF-8 -*-
# vim: expandtab sw=4 ts=4 sts=4:

# (C) 2003 - 2018 Michal Čihař <michal@cihar.com> - python-gammu
# (C) 2015 - 2021 Raspian France <raspbianfrance@gmail.com> - RaspianFrance/raspisms
# (C) 2022 - Orsiris de Jong <orsiris.dejong@netperfect.fr> - NetInvent SASU


from __future__ import print_function


__intname__ = "gammu_get_unread_sms.py"
__author__ = "Orsiris de Jong - <orsiris.dejong@netperfect.fr>"
__version__ = "2.0.2"
__build__ = "2022102501"
__compat__ = "python2.7+"


import os
import gammu
import json
import logging
from logging.handlers import RotatingFileHandler
import tempfile
from argparse import ArgumentParser
import subprocess
import re

LOG_FILE = "/var/log/{}.log".format(__intname__)
_DEBUG = os.environ.get("_DEBUG", False)
logger = logging.getLogger(__name__)


def get_logger(log_file):
    # We would normally use ofunctions.logger_utils here with logger_get_logger(), but let's keep no dependencies
    try:
        try:
            filehandler = RotatingFileHandler(
                log_file, mode="a", encoding="utf-8", maxBytes=1048576, backupCount=3
            )
        except OSError:
            try:
                temp_log_file = tempfile.gettempdir() + os.sep + __name__ + ".log"
                filehandler = RotatingFileHandler(
                    temp_log_file,
                    mode="a",
                    encoding="utf-8",
                    maxBytes=1048576,
                    backupCount=3,
                )
            except OSError as exc:
                print("Cannot create log file: %s" % exc.__str__())
                filehandler = None

        _logger = logging.getLogger()
        if _DEBUG:
            _logger.setLevel(logging.DEBUG)
        else:
            _logger.setLevel(logging.INFO)

        formatter = logging.Formatter("%(asctime)s :: %(levelname)s :: %(message)s")
        if filehandler:
            filehandler.setFormatter(formatter)
            _logger.addHandler(filehandler)
        consolehandler = logging.StreamHandler()
        consolehandler.setFormatter(formatter)
        _logger.addHandler(consolehandler)
        return _logger
    except Exception as exc:
        print("Cannot create logger instance: %s" % exc.__str__())


def get_gammu_version():
    # Quite badly coded, i'd use command_runner but I try to not have dependencies here
    try:
        proc = subprocess.Popen(
            ["LC_ALL=C gammu", "--version"], shell=True, stdout=subprocess.PIPE
        )
        stdout, _ = proc.communicate()
        version = re.search(r"Gammu version ([0-9]+)\.([0-9]+)\.([0-9]+)", str(stdout))
        # dont' bother to return version[0] since it's the whole match
        return (int(version[1]), int(version[2]), int(version[3]))
    except Exception as exc:
        logger.error("Cannot get gammu version: %s" % exc.__str__())
        return None


def get_gammu_handle(config_file):
    state_machine = gammu.StateMachine()

    if config_file:
        state_machine.ReadConfig(Filename=config_file)
    else:
        state_machine.Readconfig()
    state_machine.Init()

    return state_machine


def load_sms_from_gammu(state_machine):
    """
    The actual function that retrieves SMS via GAMMU from your modem / phone
    Also concatenates multiple SMS into single long SMS
    """
    status = state_machine.GetSMSStatus()

    remaining_sms = status["SIMUsed"] + status["PhoneUsed"] + status["TemplatesUsed"]
    logger.debug("Found %s sms" % remaining_sms)
    sms_list = []

    try:
        is_first_message = True
        while remaining_sms > 0:
            if is_first_message:
                sms = state_machine.GetNextSMS(Start=is_first_message, Folder=0)
                is_first_message = False
            else:
                sms = state_machine.GetNextSMS(Location=sms[0]["Location"], Folder=0)
            remaining_sms = remaining_sms - len(sms)
            sms_list.append(sms)

    except gammu.ERR_EMPTY:
        logger.debug("Finished reading all messages")

    # Concat multiple SMS into list of sms that go together using LinkSMS
    return gammu.LinkSMS(sms_list)


def render_sms_as_json(state_machine, sms_list, delete_sms, show_read_sms):
    """
    Provided sms_list is a list of lists of sms, eg
    sms_list = [
                   [sms],
                   [sms1, sms2],  #  When two sms are in the same list, they form a long sms
                   [sms],
               ]

    Concatenate long SMS from multiple sends and print them as JSON on stdout
    """

    for sms in sms_list:
        if sms[0]["State"] == "UnRead" or show_read_sms:
            sms_text = ""
            for to_concat_sms in sms:
                sms_text += to_concat_sms["Text"]
            print(
                json.dumps(
                    {
                        "number": sms[0]["Number"],
                        "at": str(sms[0]["DateTime"]),
                        "status": sms[0]["State"],
                        "text": sms_text,
                    }
                )
            )

            if delete_sms:
                for to_concat_sms in sms:
                    try:
                        state_machine.DeleteSMS(
                            to_concat_sms["Folder"], to_concat_sms["Location"]
                        )
                    except Exception as exc:
                        logger.error("Cannot delete sms: %s" % exc.__str__())


def main(config_file, delete_sms, show_read):
    # type: (bool, bool) -> None
    logger.debug("Running gammu receiver with config {}".format(config_file))

    try:
        # Mandatory modem config file
        # config_file = sys.argv[1]

        state_machine = get_gammu_handle(config_file)

        sms_list = load_sms_from_gammu(state_machine)
        render_sms_as_json(state_machine, sms_list, delete_sms, show_read)

    except Exception as exc:
        logger.error("Could not retrieve SMS from Gammu: %s" % exc.__str__())
        logger.debug("Trace:", exc_info=True)


if __name__ == "__main__":
    parser = ArgumentParser("Gammu SMS retriever")
    parser.add_argument(
        "gammu_config_file", type=str, nargs="?", help="Gammu config file"
    )
    parser.add_argument("--debug", action="store_true", help="Activate debugging")
    parser.add_argument(
        "-l",
        "--log-file",
        type=str,
        dest="log_file",
        default=None,
        help="Optional path to log file, defaults to /var/log",
    )
    parser.add_argument(
        "--delete", action="store_true", help="Delete messages after they've been read"
    )
    parser.add_argument(
        "--show-read", action="store_true", help="Also show already read messages"
    )

    args = parser.parse_args()

    config_file = args.gammu_config_file

    if args.log_file:
        LOG_FILE = args.log_file

    if args.debug:
        _DEBUG = args.debug

    _logger = get_logger(LOG_FILE)
    if _logger:
        logger = _logger

    delete = False
    if args.delete:
        # We need to check if we have gammu >= 1.42.0 since deleting sms with lower versions fail with:
        # Cannot delete sms: {'Text': 'The type of memory is not available or has been disabled.', 'Where': 'DeleteSMS', 'Code': 81}
        # see https://github.com/gammu/gammu/issues/460
        try:
            gammu_version = get_gammu_version()
            if gammu_version[0] > 1 or (gammu_version[0] == 1 and gammu_version[1] >= 42):
                delete = True
            else:
                logger.warning("Cannot delete SMS. You need gammu >= 1.42.0.")
        except TypeError:
            logger.warning("Cannot get gammu version. SMS Deleting might not work properly.")

    show_read = args.show_read
    main(config_file, delete, show_read)
