#!/usr/bin/env python3
"""
RVM Polling Service Runner
Main script to run RVM status polling services
"""

import os
import sys
import logging
import argparse
from datetime import datetime

# Add parent directories to path for imports
sys.path.append(os.path.join(os.path.dirname(__file__), '../../../../..'))

from internal_service_manager import RVMServiceManager

def setup_logging(log_level: str = 'INFO'):
    """Setup logging configuration"""
    logging.basicConfig(
        level=getattr(logging, log_level.upper()),
        format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
        handlers=[
            logging.StreamHandler(sys.stdout),
            logging.FileHandler('rvm_polling_service.log')
        ]
    )

def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='RVM Polling Service')
    parser.add_argument('--config', '-c', default='rvm_config.env',
                       help='Configuration file path')
    parser.add_argument('--log-level', '-l', default='INFO',
                       choices=['DEBUG', 'INFO', 'WARNING', 'ERROR'],
                       help='Log level')
    parser.add_argument('--test-connection', '-t', action='store_true',
                       help='Test connection and exit')
    parser.add_argument('--status', '-s', action='store_true',
                       help='Show status and exit')
    parser.add_argument('--force-poll', '-f', action='store_true',
                       help='Force poll all RVMs and exit')
    
    args = parser.parse_args()
    
    # Setup logging
    setup_logging(args.log_level)
    logger = logging.getLogger(__name__)
    
    try:
        # Initialize service manager
        service_manager = RVMServiceManager(args.config)
        
        if args.test_connection:
            # Test connection
            logger.info("Testing connection to MyRVM-Platform...")
            success = service_manager.test_connection()
            if success:
                logger.info("✅ Connection test successful")
                sys.exit(0)
            else:
                logger.error("❌ Connection test failed")
                sys.exit(1)
        
        elif args.status:
            # Show status
            logger.info("Getting RVM status...")
            summary = service_manager.get_service_summary()
            
            print("\n" + "="*60)
            print("RVM POLLING SERVICE STATUS")
            print("="*60)
            print(f"Running: {summary['is_running']}")
            print(f"Total RVMs: {summary['total_rvms']}")
            print(f"RVM IDs: {summary['rvm_ids']}")
            print(f"Server URL: {summary['config']['server_url']}")
            print(f"Polling Interval: {summary['config']['polling_interval']}s")
            print(f"Monitoring Interval: {summary['config']['monitoring_interval']}s")
            print(f"Last Update: {summary['last_update']}")
            
            if summary['status']:
                print("\nRVM Status:")
                for rvm_id, status in summary['status'].items():
                    print(f"  RVM {rvm_id}: {status.get('rvm_status', 'unknown')} "
                          f"(connection: {status.get('connection_status', 'unknown')})")
            
            if summary['health']:
                print("\nRVM Health:")
                for rvm_id, health in summary['health'].items():
                    if 'error' not in health:
                        print(f"  RVM {rvm_id}: {health.get('system_health', 'unknown')} "
                              f"(score: {health.get('health_score', 0):.1f})")
                    else:
                        print(f"  RVM {rvm_id}: Error - {health['error']}")
            
            print("="*60)
            sys.exit(0)
        
        elif args.force_poll:
            # Force poll
            logger.info("Starting services for force poll...")
            if service_manager.start_all_services():
                logger.info("Force polling all RVMs...")
                results = service_manager.force_poll_all()
                
                print("\n" + "="*60)
                print("FORCE POLL RESULTS")
                print("="*60)
                for rvm_id, success in results.items():
                    status = "✅ Success" if success else "❌ Failed"
                    print(f"RVM {rvm_id}: {status}")
                print("="*60)
                
                service_manager.stop_all_services()
                sys.exit(0 if all(results.values()) else 1)
            else:
                logger.error("Failed to start services")
                sys.exit(1)
        
        else:
            # Run services normally
            logger.info("Starting RVM Polling Service...")
            logger.info(f"Configuration file: {args.config}")
            logger.info(f"Log level: {args.log_level}")
            
            # Run forever
            success = service_manager.run_forever()
            sys.exit(0 if success else 1)
    
    except KeyboardInterrupt:
        logger.info("Received interrupt signal, shutting down...")
        sys.exit(0)
    except Exception as e:
        logger.error(f"Unexpected error: {e}")
        sys.exit(1)

if __name__ == '__main__':
    main()
